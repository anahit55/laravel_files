<?php

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Controller;
use App\Models\Appointments\Appointments;
use App\Models\User;
use App\Models\Users\DataStream;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function index(): Factory|View|Application
    {
        $working_hours = User::getUserWorkingHours(Auth::id())->first();
        $installedApps = Auth::user()->installedApps->pluck('id');
        $dataStreamUsers = $this->getDataStreamUsers(Auth::id(), 8, 'accepted');
        $appointments = $this->getAppointments(Auth::id(), $installedApps);

        $events = $this->formatEvents($appointments);

        return view('calendar.index', compact('working_hours', 'events', 'dataStreamUsers'));
    }

    public function filterDataStreamCalendar(Request $request)
    {
        $user_id = $request->input('dataStreamId');
        $user = User::find($user_id);
        $working_hours = User::getUserWorkingHours($user_id)->first();
        $installedApps = $user->installedApps->pluck('id');
        $appointments = $this->getAppointments($user_id, $installedApps);

        $events = $this->formatEvents($appointments);

        return response()->json([
            'working_hours' => $working_hours,
            'events' => $events,
        ]);
    }

    private function getDataStreamUsers($userId, $page, $status)
    {
        return DataStream::with('dataStreamUsers')
            ->where(['user_id' => $userId, 'page' => $page, 'status' => $status])
            ->get()
            ->pluck('dataStreamUsers')
            ->flatten();
    }

    private function getAppointments($userId, $installedApps)
    {

        $user = User::find($userId);
        $doctorsArr = [$userId, 3, 4, 29, 30, 37, 46, 50];

        if ($user->super_admin == 1){
            return Appointments::whereIn('user_id', $doctorsArr)
                ->with('consultation')
                ->get();
        }else{
            return Appointments::whereIn('app_id', $installedApps)
                ->where('user_id', $userId)
                ->with('consultation')
                ->get();
        }


    }

    private function formatEvents($appointments)
    {
        $events = [];
        foreach ($appointments as $key => $appointment) {
            $events['data'][$key] = [
                'title' => $appointment->title,
                'appointment_id' => $appointment->id,
                'start' => $appointment->date . 'T' . $appointment->start_time,
                'end' => $appointment->date . 'T' . $appointment->end_time,
                'backgroundColor' => $this->getEventColor($appointment->type),
            ];

            $consultation = $this->getConsultation($appointment);
            if ($consultation) {
                $events['data'][$key]['url'] = route($consultation['route'], $consultation['id']);
                $events['data'][$key]['consultation_id'] = $consultation['id'];
            }
        }

        return $events;
    }

    private function getEventColor($type)
    {
        $colors = [
            'default' => "#00A65A"
        ];

        return $colors[$type] ?? $colors['default'];
    }

    private function getConsultation($appointment)
    {
        if ($appointment->type == 'test1') {
            $consultation = TestFirstConsultation::where('appointment_id', $appointment->id)->first();
            if ($consultation) {
                return ['route' => 'test1.edit', 'id' => $consultation->id];
            }
        }
        elseif ($appointment->type == 'test2') {
            $consultation = TestSecondConsultation::where('appointment_id', $appointment->id)->first();
            if ($consultation) {
                return ['route' => 'test2.edit', 'id' => $consultation->id];
            }
        }
        elseif ($appointment->type == 'test3') {
            $consultation = TestThirdConsultation::where('appointment_id', $appointment->id)->first();
            if ($consultation) {
                return ['route' => 'test3.edit', 'id' => $consultation->id];
            }
        }

        return null;
    }
}

