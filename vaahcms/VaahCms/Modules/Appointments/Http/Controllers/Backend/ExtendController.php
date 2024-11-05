<?php  namespace VaahCms\Modules\Appointments\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ExtendController extends Controller
{

    //----------------------------------------------------------
    public function __construct()
    {
    }
    //----------------------------------------------------------
    public static function topLeftMenu()
    {
        $links = [];

        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);

    }
    //----------------------------------------------------------
    public static function topRightUserMenu()
    {
        $links = [];

        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------
    public static function sidebarMenu()
    {
        $links = [];


        $links[0] = [
            'icon' => 'table',
            'label'=> 'Appointment',
            'link'=> route('vh.backend.appointments'),
            'items' => [],
        ];

        $links[0]['items'][] = [
            'icon' => 'home',
            'label'=> 'Dashboard',
            'link'=> route('vh.backend.appointments'),
        ];
//        dd(\Auth::user()->hasPermission('appointments-has-access-of-patient'));
        if (\Auth::user()->hasPermission('appointments-has-access-of-doctor-section')) {
            $links[0]['items'][] = [
                'icon' => 'user',
                'link' => route('vh.backend.appointments') . "#/doctors?rows=20",
                'label' => 'Doctors'
            ];
        }
        if (\Auth::user()->hasPermission('appointments-has-access-of-patient')) {

            $links[0]['items'][] = [
                'icon' => 'users',
                'link' => route('vh.backend.appointments')."#/patients",
                'label' => 'Patients'
            ];
        }


        $links[0]['items'][]= [
            'icon' => 'calendar',
            "link" => route('vh.backend.appointments')."#/appointments",
            "label" => "Appointments",

        ];


        if(version_compare(config('vaahcms.version'), '2.0.0', '<' )){
            $links[0]['link'] = route('vh.backend.appointments');
        } else{
            $links[0]['url'] = route('vh.backend.appointments');
        }


        $response['success'] = true;
        $response['data'] = $links;

        return vh_response($response);
    }
    //----------------------------------------------------------

}
