<?php namespace VaahCms\Modules\Appointments\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Faker\Factory;
use VaahCms\Modules\Appointments\Models\Doctor;
use VaahCms\Modules\Appointments\Models\patient;
use WebReinvent\VaahCms\Libraries\VaahMail;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;

class Appointment extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'vh_appointments';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'patient_id',
        'slot_start_time',
        'date',
        'slot_end_time',
        'doctor_id',
        'status',
        'reason',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    //-------------------------------------------------
    protected $fill_except = [

    ];

    //-------------------------------------------------
    protected $appends = [
    ];

    //-------------------------------------------------
    protected function serializeDate(DateTimeInterface $date)
    {
        $date_time_format = config('settings.global.datetime_format');
        return $date->format($date_time_format);
    }

    //-------------------------------------------------
    public static function getUnFillableColumns()
    {
        return [
            'uuid',
            'created_by',
            'updated_by',
            'deleted_by',
        ];
    }
    //-------------------------------------------------
    public static function getFillableColumns()
    {
        $model = new self();
        $except = $model->fill_except;
        $fillable_columns = $model->getFillable();
        $fillable_columns = array_diff(
            $fillable_columns, $except
        );
        return $fillable_columns;
    }


    //-------------------------------------------------

    public function patient()
    {
        return $this->belongsTo(patient::class, 'patient_id', 'id')
            ->select('name', 'id');

    }

    //-------------------------------------------------
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id')
            ->select('id','name','shift_start_time','shift_end_time','specialization','phone','email');
    }
    //-------------------------------------------------
    public static function getEmptyItem()
    {
        $model = new self();
        $fillable = $model->getFillable();
        $empty_item = [];
        foreach ($fillable as $column)
        {
            $empty_item[$column] = null;
        }

        $empty_item['is_active'] = 1;

        return $empty_item;
    }

    //-------------------------------------------------

    public function createdByUser()
    {
        return $this->belongsTo(User::class,
            'created_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function updatedByUser()
    {
        return $this->belongsTo(User::class,
            'updated_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function deletedByUser()
    {
        return $this->belongsTo(User::class,
            'deleted_by', 'id'
        )->select('id', 'uuid', 'first_name', 'last_name', 'email');
    }

    //-------------------------------------------------
    public function getTableColumns()
    {
        return $this->getConnection()->getSchemaBuilder()
            ->getColumnListing($this->getTable());
    }

    //-------------------------------------------------
    public function scopeExclude($query, $columns)
    {
        return $query->select(array_diff($this->getTableColumns(), $columns));
    }

    //-------------------------------------------------
    public function scopeBetweenDates($query, $from, $to)
    {

        if ($from) {
            $from = \Carbon::parse($from)
                ->startOfDay()
                ->toDateTimeString();
        }

        if ($to) {
            $to = \Carbon::parse($to)
                ->endOfDay()
                ->toDateTimeString();
        }

        $query->whereBetween('updated_at', [$from, $to]);
    }


    //-------------------------------------------------

    public static function createItem($request)
    {
        $inputs = $request->all();

        // Validate the request inputs
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Convert the input date to a standard format (e.g., YYYY-MM-DD)
        $input_date = date('Y-m-d', strtotime($inputs['date']));

        // Check if the same patient is trying to book an appointment with the same doctor on the same day
        $existing_appointment = self::where('patient_id', $inputs['patient_id'])
            ->where('doctor_id', $inputs['doctor_id'])
            ->whereDate('date', $input_date) // Ensure it's the same date
            ->withTrashed()
            ->first();

        if ($existing_appointment) {
            $error_message = "This patient already has an appointment with this doctor on this date" . ($existing_appointment->deleted_at ? ' (in trash).' : '.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        // Fetch doctor's working hours
        $doctor = Doctor::find($inputs['doctor_id']); // Assuming there's a Doctor model
        if (!$doctor) {
            return [
                'success' => false,
                'errors' => ['Doctor not found.']
            ];
        }

        // Extract doctor's shift start and end times
        $shift_start_time = $doctor->shift_start_time; // e.g., '08:30:53'
        $shift_end_time = $doctor->shift_end_time; // e.g., '17:00:00'

        // Extract time from the input's slot_start_time
        $input_slot_start_time = date('H:i:s', strtotime($inputs['slot_start_time']));
        $input_slot_end_time = date('H:i:s', strtotime($inputs['slot_end_time']));

        // Check if the requested time is within the doctor's available time range
        if ($input_slot_start_time < $shift_start_time || $input_slot_end_time > $shift_end_time) {
            return [
                'success' => false,
                'errors' => ['The selected time is outside of the doctor\'s available time range.']
            ];
        }

        if ($input_slot_start_time >= $input_slot_end_time) {
            return [
                'success' => false,
                'errors' => ['End time must be greater than start time.']
            ];
        }

        // Check if the time slot is available for the doctor on the same date
        $existing_time_slot = self::where('doctor_id', $inputs['doctor_id'])
            ->whereDate('date', $input_date)
            ->where(function($query) use ($inputs) {
                $query->where(function ($q) use ($inputs) {
                    $q->where('slot_start_time', '<=', $inputs['slot_start_time'])
                        ->where('slot_end_time', '>=', $inputs['slot_start_time']);
                })->orWhere(function ($q) use ($inputs) {
                    $q->where('slot_start_time', '<=', $inputs['slot_end_time'])
                        ->where('slot_end_time', '>=', $inputs['slot_end_time']);
                })->orWhere(function ($q) use ($inputs) {
                    $q->where('slot_start_time', '>=', $inputs['slot_start_time'])
                        ->where('slot_end_time', '<=', $inputs['slot_end_time']);
                });
            })
            ->first();

        // Check if the existing time slot is canceled
        if ($existing_time_slot) {

            if ($existing_time_slot->status === 0) { // Assuming 'canceled' is the status

                $existing_time_slot->fill($inputs); // Update existing appointment details
                $existing_time_slot->status = 1; // Set status to booked
                $existing_time_slot->save();

                $response = self::getItem($existing_time_slot->id);
                $response['messages'][] = trans("appointment booked successfully");
                return $response;
            } else {
                $error_message = "This time slot is already booked for this doctor. Please select a different time.";
                $response['success'] = false;
                $response['errors'][] = $error_message;
                return $response;
            }
        }

        // Create a new appointment if no existing time slot is found
        $item = new self();
        $item->fill($inputs);
        $item->status = 1;

        $item->save();

        // Send appointment confirmation emails
        $subject = 'Appointment Booked - Mail';
        self::appointmentMail($inputs, $subject);

        $response = self::getItem($item->id);
        $response['messages'][] = trans("appointment booked successfully");

        return $response;
    }

    //-------------------------------------------------

    public static function formatTime($time, $timezone, $format = 'H:i A')
    {
        return Carbon::parse($time)
            ->setTimezone($timezone)
            ->format($format);
    }

    //-------------------------------------------------

    public static function appointmentMail($inputs, $subject)
    {
        $doctor = Doctor::find($inputs['doctor_id']);
        $patient = Patient::find($inputs['patient_id']);
        $timezone = Session::get('user_timezone');
        $date = Carbon::parse($inputs['date'])->toDateString();
        $slot_start_time = self::formatTime($inputs['slot_start_time'], $timezone);
        $slot_end_time = self::formatTime($inputs['slot_end_time'], $timezone);

        $message_patient = sprintf(
            'Hello, %s, You have an appointment scheduled with Dr. %s on %s from %s to %s.',
            $patient->name,
            $doctor->name,
            $date,
            $slot_start_time,
            $slot_end_time
        );

        $message_doctor = sprintf(
            'Hello, Dr. %s, You have an appointment scheduled with Patient %s on %s from %s to %s.',
            $doctor->name,
            $patient->name,
            $date,
            $slot_start_time,
            $slot_end_time
        );

        // Dispatch the emails
        VaahMail::dispatchGenericMail($subject, $message_doctor, $doctor->email);
        VaahMail::dispatchGenericMail($subject, $message_patient, $patient->email);
    }

    //-------------------------------------------------
    public function scopeGetSorted($query, $filter)
    {

        if(!isset($filter['sort']))
        {
            return $query->orderBy('id', 'desc');
        }

        $sort = $filter['sort'];


        $direction = Str::contains($sort, ':');

        if(!$direction)
        {
            return $query->orderBy($sort, 'asc');
        }

        $sort = explode(':', $sort);

        return $query->orderBy($sort[0], $sort[1]);
    }
    //-------------------------------------------------
    public function scopeIsActiveFilter($query, $filter)
    {

        if(!isset($filter['is_active'])
            || is_null($filter['is_active'])
            || $filter['is_active'] === 'null'
        )
        {
            return $query;
        }
        $is_active = $filter['is_active'];

        if($is_active === 'true' || $is_active === true)
        {
            return $query->where('is_active', 1);
        } else{
            return $query->where(function ($q){
                $q->whereNull('is_active')
                    ->orWhere('is_active', 0);
            });
        }

    }
    //-------------------------------------------------
    public function scopeTrashedFilter($query, $filter)
    {

        if(!isset($filter['trashed']))
        {
            return $query;
        }
        $trashed = $filter['trashed'];

        if($trashed === 'include')
        {
            return $query->withTrashed();
        } else if($trashed === 'only'){
            return $query->onlyTrashed();
        }

    }
    //-------------------------------------------------
    public function scopeSearchFilter($query, $filter)
    {

        if(!isset($filter['q']))
        {
            return $query;
        }
        $search_array = explode(' ',$filter['q']);

        foreach ($search_array as $search_item) {
            $query->where(function ($q1) use ($search_item) {
                $q1->whereHas('doctor', function ($query) use ($search_item) {
                    $query->where('name', 'LIKE', '%' . $search_item . '%');
                })
                    ->orWhereHas('patient', function ($query) use ($search_item) {
                        $query->where('name', 'LIKE', '%' . $search_item . '%');
                    })
                    ->orWhere(function ($query) use ($search_item) {
                        if (strtolower($search_item) === 'booked') {
                            $query->where('status', 1);
                        } elseif (strtolower($search_item) === 'cancelled') {
                            $query->where('status', 0);
                        }
                    });
            });
        }
    }

    //-------------------------------------------------
    public static function getList($request)
    {
        try {
            // Apply sorting and filters (assumed methods: getSorted, isActiveFilter, trashedFilter, searchFilter)
            $list = self::getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
            $list->with(['patient','doctor']);

            // Set the pagination rows
            $rows = config('vaahcms.per_page');
            if ($request->has('rows')) {
                $rows = $request->rows;
            }

           $list =  $list->select([
                'id',
                'doctor_id',
                'patient_id',
                'date',
                'slot_start_time',
                'slot_end_time',
                'is_active',
                'status',
                'reason',
               'created_at',
               'updated_at'

            ]);
            // Paginate the result
            $list = $list->paginate($rows);

            // Prepare the response
            $response['success'] = true;
            $response['data'] = $list;

        } catch (\Exception $e) {
            // Handle exceptions
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }
        return $response;
    }

    public static function getAppointmentList($request)
    {
        $response = []; // Initialize the response variable
        try {
            // Get all appointments without any filters
            $list = self::query()->with(['patient', 'doctor'])->get(); // Load related patient and doctor information

            // Get the total count of appointments
            $totalCount = $list->count();

            // Get the counts for booked and cancelled appointments
            $bookedCount = $list->where('status', 1)->count(); // Count of booked appointments
            $cancelledCount = $list->where('status', 0)->count(); // Count of cancelled appointments

            // Get the count of unique booked doctors and patients
            $bookedDoctorCount = $list->where('status', 1)->unique('doctor_id')->count(); // Count of unique doctors with booked appointments
            $bookedPatientCount = $list->where('status', 1)->unique('patient_id')->count(); // Count of unique patients with booked appointments

            // Prepare the response with counts inside a data object
            $response['success'] = true;
            $response['data'] = [
                'appointments' => $list, // The list of all appointments
                'counts' => [ // Counts object
                    'total_count' => $totalCount, // Total count of all appointments
                    'booked_count' => $bookedCount, // Count of booked appointments
                    'cancelled_count' => $cancelledCount, // Count of cancelled appointments
                    'booked_doctor_count' => $bookedDoctorCount, // Count of unique booked doctors
                    'booked_patient_count' => $bookedPatientCount // Count of unique booked patients
                ]
            ];

        } catch (\Exception $e) {
            // Handle exceptions
            $response['success'] = false;
            if (env('APP_DEBUG')) {
                $response['errors'][] = $e->getMessage();
                $response['hint'] = $e->getTrace();
            } else {
                $response['errors'][] = trans("vaahcms-general.something_went_wrong");
            }
        }

        return $response; // Return the response
    }




    //-------------------------------------------------
    public static function updateList($request)
    {

        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
        );

        $messages = array(
            'type.required' => trans("vaahcms-general.action_type_is_required"),
        );


        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        if(isset($inputs['items']))
        {
            $items_id = collect($inputs['items'])
                ->pluck('id')
                ->toArray();
        }

        $items = self::whereIn('id', $items_id);

        switch ($inputs['type']) {
            case 'deactivate':
                $items->withTrashed()->where(['is_active' => 1])
                    ->update(['is_active' => null]);
                break;
            case 'activate':
                $items->withTrashed()->where(function ($q){
                    $q->where('is_active', 0)->orWhereNull('is_active');
                })->update(['is_active' => 1]);
                break;
            case 'trash':
                self::whereIn('id', $items_id)
                    ->get()->each->delete();
                break;
            case 'restore':
                self::whereIn('id', $items_id)->onlyTrashed()
                    ->get()->each->restore();
                break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }

    //-------------------------------------------------
    public static function deleteList($request): array
    {
        $inputs = $request->all();

        $rules = array(
            'type' => 'required',
            'items' => 'required',
        );

        $messages = array(
            'type.required' => trans("vaahcms-general.action_type_is_required"),
            'items.required' => trans("vaahcms-general.select_items"),
        );

        $validator = \Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {

            $errors = errorsToArray($validator->errors());
            $response['success'] = false;
            $response['errors'] = $errors;
            return $response;
        }

        $items_id = collect($inputs['items'])->pluck('id')->toArray();
        self::whereIn('id', $items_id)->forceDelete();

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
     public static function listAction($request, $type): array
    {

        $list = self::query();

        if($request->has('filter')){
            $list->getSorted($request->filter);
            $list->isActiveFilter($request->filter);
            $list->trashedFilter($request->filter);
            $list->searchFilter($request->filter);
        }

        switch ($type) {
            case 'activate-all':
                $list->withTrashed()->where(function ($q){
                    $q->where('is_active', 0)->orWhereNull('is_active');
                })->update(['is_active' => 1]);
                break;
            case 'deactivate-all':
                $list->withTrashed()->where(['is_active' => 1])
                    ->update(['is_active' => null]);
                break;
            case 'trash-all':
                $list->get()->each->delete();
                break;
            case 'restore-all':
                $list->onlyTrashed()->get()
                    ->each->restore();
                break;
            case 'delete-all':
                $list->forceDelete();
                break;
            case 'create-100-records':
            case 'create-1000-records':
            case 'create-5000-records':
            case 'create-10000-records':

            if(!config('appointments.is_dev')){
                $response['success'] = false;
                $response['errors'][] = 'User is not in the development environment.';

                return $response;
            }

            preg_match('/-(.*?)-/', $type, $matches);

            if(count($matches) !== 2){
                break;
            }

            self::seedSampleItems($matches[1]);
            break;
        }

        $response['success'] = true;
        $response['data'] = true;
        $response['messages'][] = trans("vaahcms-general.action_successful");

        return $response;
    }
    //-------------------------------------------------
    public static function getItem($id)
    {

        $item = self::where('id', $id)
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser','doctor','patient',])
            ->withTrashed()
            ->first();

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;
            return $response;
        }
        $response['success'] = true;
        $response['data'] = $item;



        return $response;

    }
    //-------------------------------------------------
    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        // Validate the request inputs
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Convert the input date to a standard format (e.g., YYYY-MM-DD)
        $input_date = date('Y-m-d', strtotime($inputs['date']));

        // Check if the same patient is trying to book an appointment with the same doctor on the same day
        $existing_appointment = self::where('patient_id', $inputs['patient_id'])
            ->where('doctor_id', $inputs['doctor_id'])
            ->whereDate('date', $input_date)
            ->where('id', '!=', $id) // Exclude the current appointment
            ->withTrashed()
            ->first();

        if ($existing_appointment) {
            $error_message = "This patient already has an appointment with this doctor on this date" . ($existing_appointment->deleted_at ? ' (in trash).' : '.');
            $response['success'] = false;
            $response['errors'][] = $error_message;
            return $response;
        }

        // Fetch doctor's working hours
        $doctor = Doctor::find($inputs['doctor_id']);
        if (!$doctor) {
            return [
                'success' => false,
                'errors' => ['Doctor not found.']
            ];
        }

        // Extract doctor's shift start and end times
        $shift_start_time = $doctor->shift_start_time; // e.g., '08:30:53'
        $shift_end_time = $doctor->shift_end_time; // e.g., '17:00:00'

        // Extract time from the input's slot_start_time
        $input_slot_start_time = date('H:i:s', strtotime($inputs['slot_start_time']));
        $input_slot_end_time = date('H:i:s', strtotime($inputs['slot_end_time']));

        // Check if the requested time is within the doctor's available time range
        if ($input_slot_start_time < $shift_start_time || $input_slot_end_time > $shift_end_time) {
            return [
                'success' => false,
                'errors' => ['The selected time is outside of the doctor\'s available time range.']
            ];
        }

        // Check if slot_start_time is less than slot_end_time
        if ($input_slot_start_time >= $input_slot_end_time) {
            return [
                'success' => false,
                'errors' => ['End time must be greater than start time.']
            ];
        }

        // Check if the time slot is available for the doctor on the same date
        $existing_time_slot = self::where('doctor_id', $inputs['doctor_id'])
            ->whereDate('date', $input_date)
            ->where(function($query) use ($inputs) {
                $query->where(function ($q) use ($inputs) {
                    $q->where('slot_start_time', '<=', $inputs['slot_start_time'])
                        ->where('slot_end_time', '>=', $inputs['slot_start_time']);
                })->orWhere(function ($q) use ($inputs) {
                    $q->where('slot_start_time', '<=', $inputs['slot_end_time'])
                        ->where('slot_end_time', '>=', $inputs['slot_end_time']);
                })->orWhere(function ($q) use ($inputs) {
                    $q->where('slot_start_time', '>=', $inputs['slot_start_time'])
                        ->where('slot_end_time', '<=', $inputs['slot_end_time']);
                });
            })
            ->first();

        // Check if the existing time slot is canceled
        if ($existing_time_slot) {
            if ($existing_time_slot->status === 0) { // Assuming 'canceled' is the status
                // Update existing appointment details
                $existing_time_slot->fill($inputs);
                $existing_time_slot->status = 1; // Set status to booked
                $existing_time_slot->save();

                $response = self::getItem($existing_time_slot->id);
                $response['messages'][] = trans("appointment updated successfully");
                return $response;
            } else {
                $error_message = "This time slot is already booked for this doctor. Please select a different time.";
                $response['success'] = false;
                $response['errors'][] = $error_message;
                return $response;
            }
        }

        // Find and update the appointment
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = "Appointment not found.";
            return $response;
        }

        // Fill and save the updated appointment data
        $item->fill($inputs);
        $item->status = 1;
        $item->reason = "Time Updated by Patient";
        $item->save();

        // Send an appointment update email
        $subject = 'Appointment Update - Mail';
        self::appointmentMail($inputs, $subject);

        // Prepare and return the response
        $response = self::getItem($item->id);
        $response['messages'][] = trans("appointment updated successfully");

        return $response;
    }

    //-------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        // Fetch the item, including trashed records
        $item = self::where('id', $id)->withTrashed()->first();

        // Check if the item exists
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }

        $user = \Auth::user();

        if ($user) {
            // If the user exists, use the display_name or set 'Admin' if it's null
            $name = $user->display_name ?? 'Admin';
        } else {
            // Fallback in case the user is not authenticated (optional)
            $name = 'Admin';
        }
        $item->reason = "Cancelled by  $name";

        // Update the status to 0 (soft delete behavior)
        $item->status = 0;
        $item->save();

        $subject='Appointment Cancelled - Mail';
        $patient = Patient::find($item['patient_id']);
        $doctor = Doctor::find($item['doctor_id']);
        $date = Carbon::parse($item['date'])->toDateString();

        $start_time = $item['slot_start_time'];
        $end_time = $item['slot_end_time'];
        $appointment_url = vh_get_assets_base_url() . '/backend/appointments#/appointments';

        // Updated message with link
        $message = sprintf(
            'Hello %s,<br>Your appointment with Dr. %s on %s from %s to %s has been cancelled by the doctor.<br><br>
        You can <a href="%s" style="text-decoration:none;">
        <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            Book Another Appointment
        </button></a>',
            $patient->name,
            $doctor->name,
            $date,
            $start_time,
            $end_time,
            $appointment_url
        );

        VaahMail::dispatchGenericMail($subject, $message, $patient->email);

        // Prepare the response
        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("Appointment cancel");

        return $response;
    }

    //-------------------------------------------------
    public static function itemAction($request, $id, $type): array
    {
        switch($type)
        {
            case 'activate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => 1]);
                break;
            case 'deactivate':
                self::where('id', $id)
                    ->withTrashed()
                    ->update(['is_active' => null]);
                break;
            case 'trash':
                self::find($id)
                    ->delete();
                break;
            case 'restore':
                self::where('id', $id)
                    ->onlyTrashed()
                    ->first()->restore();
                break;
        }

        return self::getItem($id);
    }
    //-------------------------------------------------

    public static function validation($inputs)
    {

        $rules = array(
//            'uuid' => 'required|max:150',
            'patient_id' => 'required|max:20',
            'slot_start_time' => 'required|max:100',
            'date' => 'required|max:100',
            'slot_end_time' => 'required|max:100',
            'doctor_id' => 'required|max:20',
        );

        $validator = \Validator::make($inputs, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors();
            $response['success'] = false;
            $response['errors'] = $messages->all();
            return $response;
        }

        $response['success'] = true;
        return $response;

    }

    //-------------------------------------------------
    public static function getActiveItems()
    {
        $item = self::where('is_active', 1)
            ->withTrashed()
            ->first();
        return $item;
    }

    //-------------------------------------------------
    //-------------------------------------------------
    public static function seedSampleItems($records=100)
    {

        $i = 0;

        while($i < $records)
        {
            $inputs = self::fillItem(false);

            $item =  new self();
            $item->fill($inputs);
            $item->save();

            $i++;

        }

    }


    //-------------------------------------------------
    public static function fillItem($is_response_return = true)
    {
        $request = new Request([
            'model_namespace' => self::class,
            'except' => self::getUnFillableColumns()
        ]);
        $fillable = VaahSeeder::fill($request);
        if(!$fillable['success']){
            return $fillable;
        }
        $inputs = $fillable['data']['fill'];

        $faker = Factory::create();

        /*
         * You can override the filled variables below this line.
         * You should also return relationship from here
         */

        if(!$is_response_return){
            return $inputs;
        }

        $response['success'] = true;
        $response['data']['fill'] = $inputs;
        return $response;
    }

    //-------------------------------------------------
    //-------------------------------------------------
    //-------------------------------------------------



}
