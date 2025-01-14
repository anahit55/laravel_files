<?php

namespace App\Http\Controllers\Marketplaces\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMarketplaceConsultationRequest;
use Carbon\Carbon;
use App\Imports\{ExternalDoctorConsultationsImport};
use App\Mail\{ClientInformFromDoctor, DoctorCallBackEmail, DoctorInformMail, OnlineConsultationMail};
use App\Models\Appointments\Appointments;
use App\Models\Apps\Doctor\{Consultations,DoctorCalls};
use App\Models\Clients\Client;
use App\Models\CompanyManagement\App;
use App\Models\Invoices\Invoice;
use App\Models\Languages\Language;
use App\Models\Marketplaces\Doctor\Consultations\Consultation;
use App\Models\Marketplaces\Doctor\Information\{AppointmentType,
    InsuranceCompany,
    Pharmacy,
    Prescription,
    Status,
    Hotel,
    TdDoctor,
    TdEmergencyCenter};
use App\Models\User;
use App\Services\{GeocodingService, QuickChartService, DoctorCommunicationService, DoctorStatisticService};
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\{Factory,View};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\{JsonResponse,RedirectResponse,Request,Response};
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\{Auth,DB,Log,Mail};
use Illuminate\Database\Eloquent\Collection;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class MarketplaceController extends Controller
{

    private GeocodingService $geocodingService;
    private QuickChartService $quickChartService;
    private DoctorStatisticService $doctorStatisticService;
    private DoctorCommunicationService $doctorCommunicationService;

    public function __construct(
        GeocodingService $geocodingService,
        QuickChartService $quickChartService,
        DoctorStatisticService $doctorStatisticService,
        DoctorCommunicationService $doctorCommunicationService
    )
    {
        $this->geocodingService = $geocodingService;
        $this->quickChartService = $quickChartService;
        $this->doctorStatisticService = $doctorStatisticService;
        $this->doctorCommunicationService = $doctorCommunicationService;
    }

    /**
     * @return Factory|View|Application
     */
    public function index(): Factory|View|Application
    {
        $marketplaceId = 1;
        $appointmentTypeId = request('appointment_type');
        $appointmentType = AppointmentType::find($appointmentTypeId);

        // Fetch doctors based on conditions
        $doctors = $this->fetchDoctors($marketplaceId, $appointmentType);

        // Set marketplace nicknames for doctors
        $this->setMarketPlaceNicknames($doctors, $marketplaceId);

        // Fetch appointment types for doctors
        $appointmentTypes = $this->fetchAppointmentTypes($doctors);

        $insurance_companies = InsuranceCompany::all();

        return view('marketplaces.doctor.marketplace', compact('doctors', 'appointmentTypes', 'insurance_companies'));
    }

    /**
     * @param int $marketplaceId
     * @param $appointmentType
     * @return User[]|Builder[]|Collection|_IH_User_C|_IH_User_QB[]
     */
    private function fetchDoctors(int $marketplaceId, $appointmentType)
    {
        $query = User::with([
            'marketplaces' => function ($query) {
                $query->withPivot('nickname');
            },
            'location'
        ]);

        if ($appointmentType) {
            if ($appointmentType->additional_location_confirm == 1) {
                $query->whereHas('marketplaces', function ($query) use ($marketplaceId) {
                    $query->where('app_id', $marketplaceId);
                })->where('additional_location', $appointmentType->additional_location);
            } else {
                $query->whereHas('marketplaces', function ($query) use ($marketplaceId) {
                    $query->where('app_id', $marketplaceId);
                })->where('location_id', $appointmentType->location_id)
                    ->where('additional_location_confirm', '=', 0);
            }
        } else {
            $query->whereHas('marketplaces', function ($query) use ($marketplaceId) {
                $query->where('app_id', $marketplaceId);
            });
        }

        return $query->get();
    }

    /**
     * @param Collection $doctors
     * @param int $marketplaceId
     * @return void
     */
    private function setMarketPlaceNicknames(Collection $doctors, int $marketplaceId): void
    {
        $doctors->each(function ($doctor) use ($marketplaceId) {
            $doctor->marketplaceNickname = $doctor->getNicknameByMarketplaceId($marketplaceId);
        });
    }

    /**
     * @param Collection $doctors
     * @return Collection|\Illuminate\Support\Collection
     */
    private function fetchAppointmentTypes(Collection $doctors)
    {
        return $doctors->mapWithKeys(function ($doctor) {
            if ($doctor->additional_location_confirm == 1) {
                $types = AppointmentType::where('additional_location', $doctor->additional_location)
                    ->whereHas('doctors', function ($query) use ($doctor) {
                        $query->where('users.id', $doctor->id);
                    })
                    ->get();
                return [$doctor->id => $types];
            } else {
                if ($doctor->location) {
                    $types = AppointmentType::where('location_id', $doctor->location_id)
                        ->whereHas('doctors', function ($query) use ($doctor) {
                            $query->where('users.id', $doctor->id);
                        })
                        ->get();
                    return [$doctor->id => $types];
                }
            }
            return [$doctor->id => collect()];
        });
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function consultations(Request $request): Factory|View|Application
    {
        $category = 0; //Consultation
        return $this->getConsultationsByCategory($category, $request);
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function prescriptions(Request $request): Factory|View|Application
    {
        $category = 1; // Prescription
        return $this->getConsultationsByCategory($category, $request);
    }

    /**
     * @param int $category
     * @param Request $request
     * @return Application|Factory|View
     */
    public function getConsultationsByCategory(int $category, Request $request): Application|Factory|View
    {
        $consultations = Consultation::with('invoices')
            ->where('category', '=', $category)->get();

        $activeTab = $request->input('tab', 'consultations');

        $myConsultations = Consultation::with('invoices')
            ->where('category', '=', $category)
            ->where('doctor_id', Auth::id())->get();
        $invoices = $consultations->pluck('invoices')->flatten();

        $consultationInvoicesToPatient = $consultations->whereNotNull('invoice_1_id')
            ->pluck('consultationInvoicesToPatient')->where('user_id', Auth::id())
            ->flatten();

        $consultationMyInvoices = $consultations->whereNotNull('invoice_2_id')
            ->pluck('consultationMyInvoices')->where('user_id', Auth::id())
            ->flatten();

        $commissionInvoices = $consultations->whereNotNull('invoice_3_id')
            ->pluck('commissionInvoice')->where('user_id', Auth::id())
            ->flatten();

        $appointment_types = AppointmentType::all();
        $working_hours = User::getUserWorkingHours(Auth::id())->first();

        $doctorsArr = [1, 3, 4, 29, 30, 37, 46, 50];

        if (Auth::user()->super_admin == 1) {
            $appointments = Appointments::whereIn('user_id', $doctorsArr)
                ->where('type', 'doctor')
                ->with('consultation')
                ->get();
        } else {
            $appointments = Appointments::where('user_id', Auth::id())
                ->where('type', 'doctor')
                ->with('consultation')
                ->get();
        }

        $events = [];
        foreach ($appointments as $key => $appointment) {
            $events['data'][$key]['title'] = $appointment->title;
            $events['data'][$key]['appointment_id'] = $appointment->id;
            $events['data'][$key]['start'] = $appointment->date . 'T' . $appointment->start_time;
            $events['data'][$key]['end'] = $appointment->date . 'T' . $appointment->end_time;
            $events['data'][$key]['backgroundColor'] = "#e7e7ff";
            $events['data'][$key]['borderColor'] = "rgba(105,108,255,.15)";
            $events['data'][$key]['textColor'] = "#696cff";
            $consultation = Consultation::where('appointment_id', $appointment->id)->first();
            if ($consultation) {
                $events['data'][$key]['url'] = route('consultation.edit', $consultation->id);
                $events['data'][$key]['consultation_id'] = $consultation->id;
            }

        }

        $statuses = Invoice::statuses();
        $clients = $this->getClients();
        $consultations = Consultation::with('invoices')
            ->where('category', '=', $category)->orderBy('id', 'desc')->paginate(50, ['*'], 'consultations');

        return view('marketplaces.doctor.consultations.index', compact('category', 'consultations',
            'invoices', 'statuses', 'myConsultations', 'appointment_types', 'working_hours', 'events', 'clients',
            'commissionInvoices', 'consultationMyInvoices', 'consultationInvoicesToPatient', 'activeTab'));
    }

    /**
     * @param Request $request
     * @return Factory|View|Application
     */
    public function information(Request $request): Factory|View|Application
    {
        $hotels = Hotel::paginate(50, ['*'], 'hotels');

        $activeTab = $request->input('tab', 'appointment-types');

        $prescriptions = Prescription::all();
        $pharmacies = Pharmacy::all();
        $insurance_companies = InsuranceCompany::all();
        $statuses = Status::all();
        $appointment_types = AppointmentType::all();
        $td_doctors = TdDoctor::paginate(50, ['*'], 'doctors');
        $td_emergency_centers = TdEmergencyCenter::paginate(50, ['*'], 'emergency_centers');

        return view('marketplaces.doctor.information.index', compact('hotels', 'prescriptions',
            'pharmacies', 'insurance_companies', 'statuses', 'appointment_types', 'activeTab','td_doctors','td_emergency_centers'));
    }

    public function getClients()
    {
        $clients = Client::whereIn('email', function ($query) {
            $query->select('email')->distinct()->from('td_consultations');
        })->get();

        $clients->transform(function ($client) {
            $balance = $client->invoices->sum('balance');
            $client->balance = $balance;
            return $client;
        });

        return $clients;
    }

    /**
     * @param Consultation $prescription
     * @return Response|Application|ResponseFactory
     */
    public function generatePrescriptionPDF(Consultation $prescription): Response|Application|ResponseFactory
    {
        $pdf = new Dompdf();
        $pdf->loadHtml(view('marketplaces.doctor.pdf.prescription', compact('prescription')));
        $pdf->setPaper('A4');
        $pdf->render();
        $pdfContent = $pdf->output();

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . "prescription-$prescription->id.pdf" . '"',
        ];

        return response($pdfContent, 200, $headers);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function exportPdf(Request $request): Response
    {
        $selectedYear = $request->input('year');
        $selectedQuarter = $request->input('quarter');

        $data = DB::table('td_calls')
            ->whereYear('date', $selectedYear)
            ->whereRaw("QUARTER(date) = ?", [$selectedQuarter])
            ->get();

        $pdf = PDF::loadView('marketplaces.doctor.pdf.calls', compact('data'))
            ->setPaper([0, 0, 800, 1200], 'landscape');

        return $pdf->download('calls_report.pdf');
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function exportTdConsultationPdf(Request $request): Response
    {
        $title = $request->input('title');
        $text = $request->input('text');
        $selectedYear = $request->input('year');
        $selectedQuarter = $request->input('quarter');

        $pages = [];

        $chartDataArray = $this->allChartsData($selectedYear, $selectedQuarter);
        $chartImages = [];
        foreach ($chartDataArray as $chartData) {
            $chartConfigArray = $this->quickChartService->chartConfigArray($chartData);

            $imagePath = $this->quickChartService->generateChartImage($chartConfigArray);
            $chartImage = Image::make($imagePath)->encode('data-url')->__toString();
            $chartImages[] = $chartImage;
        }

        for ($index = 0; $index < 3; $index++) {
            if ($index != 2) {
                $content = view('marketplaces.doctor.pdf.chart', compact('index', 'title', 'text'))->render();
                $pages[] = $content;
            } else {
                foreach ($chartImages as $chartImage) {
                    $content = view('marketplaces.doctor.pdf.chart', compact('index', 'chartImage'))->render();
                    $pages[] = $content;
                }
            }
        }

        $pdf = new Dompdf();
        $pdf->loadHtml(implode('', $pages));
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();

        $pdfContent = $pdf->output();
        $response = new Response($pdfContent);

        // Set the response headers for streaming
        $response->header('Content-Type', 'application/pdf');
        $response->header('Content-Disposition', 'inline; filename="external-consultation-charts.pdf"');

        return $response;
    }


    /**
     * @param $year
     * @param $quarter
     * @return array
     */
    public function allChartsData($year, $quarter): array
    {

        $chartData1 = $this->doctorStatisticService->consultationsYearChartData();
        $chartData2 = $this->doctorStatisticService->appointmentTypeChartDataByYear();
        $chartData3 = $this->doctorStatisticService->combinationChartData();
        $chartData4 = $this->doctorStatisticService->consultationsQuarterChartData($year);
        $chartData5 = $this->doctorStatisticService->statisticsMonthChartData(Consultation::getConsultationsByMonth($year, $quarter, 0), $quarter, 'Online consultations');
        $chartData6 = $this->doctorStatisticService->appointmentTypeMonthChartData($year, $quarter);
        $chartData7 = $this->doctorStatisticService->statisticsTimeChartData(Consultation::getConsultationsByTime(0), 'Online consultations');

        $chartData8 = $this->doctorCommunicationService->quarterChartData($year);
        $chartData9 = $this->doctorCommunicationService->totalQuarterChartData($year);
        $chartData10 = $this->doctorCommunicationService->weekdayChartData(DoctorCalls::getCallsByWeekday($year, $quarter));
        $chartData11 = $this->doctorCommunicationService->durationChartData(DoctorCalls::getCallsByDuration($year, $quarter));
        $chartData12 = $this->doctorCommunicationService->codeChartData(DoctorCalls::getCallsByCode($year, $quarter, 'Inbound'), 'Inbound');
        $chartData13 = $this->doctorCommunicationService->codeChartData(DoctorCalls::getCallsByCode($year, $quarter, 'Outbound'), 'Outbound');
        $chartData14 = $this->doctorCommunicationService->timeChartData(DoctorCalls::getCallsByTime($year, $quarter, 'Inbound'), 'Inbound');
        $chartData15 = $this->doctorCommunicationService->timeChartData(DoctorCalls::getCallsByTime($year, $quarter, 'Outbound'), 'Outbound');
        $chartData16 = $this->doctorCommunicationService->monthChartData(DoctorCalls::getCallsByMonth($year, $quarter, 'Inbound'), 'Inbound', $quarter);
        $chartData17 = $this->doctorCommunicationService->monthChartData(DoctorCalls::getCallsByMonth($year, $quarter, 'Outbound'), 'Outbound', $quarter);
        $chartData18 = $this->doctorCommunicationService->afterWorkingHoursChartData(DoctorCalls::getCallsAfterWorkingHours($year, $quarter, 'Inbound'), 'Inbound');
        $chartData19 = $this->doctorCommunicationService->connectionChartData(DoctorCalls::getCallsByConnection($year, $quarter, 'Inbound'), 'Inbound');

        $chartData20 = $this->doctorCommunicationService->yearChartData([2017, 2018, 2019, 2020]);
        $chartData21 = $this->doctorCommunicationService->yearChartData([2021, 2022, 2023, 2024]);

        $chartData22 = $this->doctorCommunicationService->practiceChartData('Outbound');

        return [$chartData1, $chartData2, $chartData3, $chartData4, $chartData5, $chartData6, $chartData7, $chartData8,
            $chartData9, $chartData10, $chartData11, $chartData12, $chartData13, $chartData14, $chartData15,
            $chartData16, $chartData17, $chartData18, $chartData19, $chartData20, $chartData21, $chartData22];
    }

    /**
     * @param StoreMarketplaceConsultationRequest $request
     * @return RedirectResponse
     */
    public function marketplaceConsultationSchedule(StoreMarketplaceConsultationRequest $request): RedirectResponse
    {
        $validatedData = $request->validated();
        $validatedData['url_key'] = random_string('alnum', 32);
        $validatedData['terms_and_conditions'] = $request->has('terms_and_conditions') ? 1 : 0;

        $consultation = Consultation::create($validatedData);
        $consultationCardData = [
            'insurance_card_front_url' => asset('uploads/doctor/consultation/card/no_image.jpg'),
            'insurance_card_back_url' => asset('uploads/doctor/consultation/card/no_image.jpg'),
            'insurance_card_number' => $validatedData['insurance_card_number'] ?? '',
            'insurance_card_date' => $validatedData['insurance_card_date'] ?? '',
        ];
        if (isset($validatedData['insurance_company_id']) && !is_null($validatedData['insurance_company_id'])) {

            if ($request->hasFile('insurance_card_front')) {
                $insurance_card_front = $request->file('insurance_card_front');
                $fileName1 = uniqid() . '.' . $insurance_card_front->getClientOriginalExtension();
                $resizedImage1 = Image::make($insurance_card_front)
                    ->resize(400, 300)
                    ->encode($insurance_card_front->getClientOriginalExtension(), 50);
                $resizedImage1->save(public_path('uploads/doctor/consultation/card/' . $fileName1));
            }

            if ($request->hasFile('insurance_card_back')) {
                $insurance_card_back = $request->file('insurance_card_back');
                $fileName2 = uniqid() . '.' . $insurance_card_back->getClientOriginalExtension();
                $resizedImage2 = Image::make($insurance_card_back)
                    ->resize(400, 300)
                    ->encode($insurance_card_back->getClientOriginalExtension(), 50);
                $resizedImage2->save(public_path('uploads/doctor/consultation/card/' . $fileName2));
            }

            $consultation->cards()->create([
                'insurance_card_front' => $fileName1,
                'insurance_card_back' => $fileName2,
                'identify_card_front' => $fileName3 ?? '',
                'identify_card_back' => $fileName4 ?? '',
                'insurance_card_number' => $validatedData['insurance_card_number'],
                'insurance_card_date' => $validatedData['insurance_card_date'],
            ]);

            $consultationCardData = [
                'insurance_card_front_url' => asset('uploads/doctor/consultation/card/' . ($fileName1 ?? 'no_image.jpg')),
                'insurance_card_back_url' => asset('uploads/doctor/consultation/card/' . ($fileName2 ?? 'no_image.jpg')),
                'insurance_card_number' => $validatedData['insurance_card_number'] ?? '',
                'insurance_card_date' => $validatedData['insurance_card_date'] ?? '',
            ];
        }

        $consultation->appointment_types()->attach($request->input('appointment_type_id'),
            ['created_at' => now(), 'updated_at' => now()]);
        $first_appointment_type = $consultation->appointment_types->first();

        if (!isset($validatedData['insurance_company_id']) || is_null($validatedData['insurance_company_id'])) {
            $client = Client::where('email', $validatedData['email'])->first();
            if (!$client) {
                $client = $this->createClient($consultation, 'patient');
            }

        } else {
            $insurance_company_id = $validatedData['insurance_company_id'];
            $insurance_company = InsuranceCompany::find($insurance_company_id);
            $client = Client::where('email', $insurance_company->email)->first();
            if (!$client) {
                $client = $this->createClient($insurance_company, 'insurance_company');
            }
        }

        $appointmentData = [
            'start_time' => $validatedData['start_time'],
            'end_time' => $validatedData['end_time'],
            'date' => $validatedData['date'],
            'company_id' => 3, // Doctor company
            'title' => $first_appointment_type->name,
            'client_id' => $client->id,
            'user_id' => $consultation->doctor_id,
            'price' => $consultation->appointment_types->sum('price'),
            'address' => $first_appointment_type->address,
            'zip' => $first_appointment_type->zip,
            'city' => $first_appointment_type->city,
            'country' => $first_appointment_type->country,
            'type' => $validatedData['portal'] ?? 'doctor',
            'url_key' => random_string('alnum', 32),
            'app_id' => $validatedData['portal']
        ];

        $appointment = Appointments::create($appointmentData);
        $consultation->update(['appointment_id' => $appointment->id]);

        if ($validatedData['call_checkbox_input'] == 'true') {
            if (!$this->callBackToDoctor($validatedData)) {
                return redirect()->back()->with('error', 'Failed');
            }
        } else {
            $doctorEmails = collect([
                $consultation->doctor->email,
                $consultation->doctor->email_backup,
                $consultation->doctor->email_backup_1,
            ])->filter()->all(); // Remove null values

            if ($first_appointment_type->is_mailable) {
                // Send email to the doctor and backup emails
                Mail::to($doctorEmails)
                    ->send(new DoctorInformMail($consultation, $appointmentData, $first_appointment_type, $consultationCardData));

                // Send email to the client
                Mail::to($validatedData['email'])
                    ->send(new ClientInformFromDoctor($consultation, $appointmentData, $first_appointment_type, $consultationCardData));
            }
        }

        return redirect($redirect_url);
    }

    /**
     * @param $data
     * @param $type
     * @return mixed
     */
    private function createClient($data, $type): mixed
    {
        if ($type == 'insurance_company') {
            $data->created_by = 1;
            $data->url_key = random_string('alnum', 32);
            $data = $data->toArray();
            return Client::create($data);
        } else {
            $data->created_by = 1;
            $data->url_key = random_string('alnum', 32);
            $data = $data->toArray();
            return Client::create($data);
        }
    }

    public function geoLocationGuest(): Factory|View|Application
    {
        $languages = Language::all();
        return view('marketplaces.doctor.consultations.guest.geo_location',
            compact('languages'));
    }

    /**
     * @throws GuzzleException
     */
    public function getCurrentAddress(Request $request)
    {
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $address = $this->geocodingService->getAddressFromCoordinates($latitude, $longitude);
        return response()->json(['address' => $address]);
    }

    /**
     * @param mixed $validatedData
     * @return bool
     */
    private function callBackToDoctor(mixed $validatedData)
    {
        $doctor = User::find($validatedData['doctor_id']);
        if (Mail::to([$doctor->email, $validatedData['email']])->send(new DoctorCallBackEmail($validatedData))) {
            return true;
        } else {
            return false;
        }
    }

}
