<?php namespace VaahCms\Modules\Appointments\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Faker\Factory;
use WebReinvent\VaahCms\Models\VaahModel;
use WebReinvent\VaahCms\Traits\CrudWithUuidObservantTrait;
use WebReinvent\VaahCms\Models\User;
use WebReinvent\VaahCms\Libraries\VaahSeeder;
use WebReinvent\VaahCms\Libraries\VaahMail;
use Illuminate\Support\Facades\Session;

class Doctor extends VaahModel
{

    use SoftDeletes;
    use CrudWithUuidObservantTrait;

    //-------------------------------------------------
    protected $table = 'doctor';
    //-------------------------------------------------
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    //-------------------------------------------------
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'specialization',
        'phone',
        'price',
        'shift_start_time',
        'shift_end_time',
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
        'appointments_count',
        'appointments_list'
    ];

    //-------------------------------------------------

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'id');

    }
    public function getAppointmentsCountAttribute(): int
    {
        return $this->appointments()->whereNotIn('status', [0, 2])->count();
    }
    public function getAppointmentsListAttribute(): array
    {
        // Eager load the patient details with the appointments
        return $this->appointments()->with('patient')->get()->toArray();
    }
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

    //-------------------------------------------------
    public static function createItem($request)
    {
        $inputs = $request->all();

        // Basic validation
        $validation = self::validation($inputs);
        if (!$validation['success']) {
            return $validation;
        }

        // Check if name exists
        $item = self::where('name', $inputs['name'])->withTrashed()->first();
        if ($item) {
            $error_message = "This name already exists" . ($item->deleted_at ? ' in trash.' : '.');
            $response['success'] = false;
            $response['messages'][] = $error_message;
            return $response;
        }

        // Ensure shift_start_time is less than shift_end_time
        if (isset($inputs['shift_start_time']) && isset($inputs['shift_end_time'])) {
            $start_time = strtotime($inputs['shift_start_time']);
            $end_time = strtotime($inputs['shift_end_time']);

            if ($start_time >= $end_time) {
                $response['success'] = false;
                $response['errors'][] = "Shift start time must be earlier than shift end time.";
                return $response;
            }
        }

        // Create new item
        $item = new self();
        $item->fill($inputs);
        $item->save();

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");

        return $response;
    }


    //-------------------------------------------------
    protected function shiftStartTime(): Attribute
    {

        return Attribute::make(
            get: function (string $value = null,) {
                $timezone = Session::get('user_timezone');

                return Carbon::parse($value)
                    ->setTimezone($timezone)
                    ->format('H:i');
            },
        );
    }
    public static function formatTime($time, $format = 'H:i:s A')
    {
        return Carbon::parse($time)
            ->setTimezone("ASIA/KOLKATA")
            ->format($format);
    }

    //-------------------------------------------------
    protected function shiftEndTime(): Attribute
    {
        return Attribute::make(
            get: function (string $value = null,) {
                $timezone = Session::get('user_timezone');
                return Carbon::parse($value)
                    ->setTimezone($timezone)
                    ->format('H:i');
            },
        );
    }
    //-------------------------------------------------
    //-------------------------------------------------
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
        foreach ($search_array as $search_item){
            $query->where(function ($q1) use ($search_item) {
                $q1->where('name', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('email', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('specialization', 'LIKE', '%' . $search_item . '%')
                    ->orWhere('id', 'LIKE', $search_item . '%');
            });
        }

    }
    //-------------------------------------------------
    public static function getList($request)
    {
        // Start by applying sorting to the list
        $list = self::getSorted($request->filter);

        $list->isActiveFilter($request->filter);
        $list->trashedFilter($request->filter);
        $list->searchFilter($request->filter);

        // Select only specific columns
        $list->select([
            'email',
            'id',
            'is_active',
            'shift_start_time',
            'shift_end_time',
            'phone',
            'name',
            'price',
            'specialization',
            'updated_at',

        ]);



        // Set default rows per page
        $rows = config('vaahcms.per_page');

        // Override rows per page if provided in the request
        if ($request->has('rows') && is_numeric($request->rows)) {
            $rows = $request->rows;
        }

        // Paginate the list
        $list = $list->paginate($rows);

        // Prepare the response
        $response['success'] = true;
        $response['data'] = $list;

        return $response;
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

        // Get the IDs of the items to delete
        $items_id = collect($inputs['items'])->pluck('id')->toArray();

        // Loop through each item (doctor) and cancel associated appointments
        foreach ($items_id as $id) {
            $item = self::where('id', $id)->withTrashed()->first();
            if (!$item) {
                $response['success'] = false;
                $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
                continue; // Skip to the next ID if the record does not exist
            }

            // Cancel appointments associated with the doctor being deleted
            $appointments = Appointment::where('doctor_id', $id)
                ->where('patient_id', '!=', null)
                ->get();

            foreach ($appointments as $appointment) {
                $appointment->status = 0; // Assuming 0 means cancelled
                $appointment->reason = 'Doctor has been deleted';
                $appointment->save();

                // Optionally send notification email to patient
                $subject = 'Appointment Cancelled - Doctor Deleted';
                self::sendCancellationMail($appointment, $subject);
            }

            // Delete the doctor record
            $item->forceDelete();
        }

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
            ->with(['createdByUser', 'updatedByUser', 'deletedByUser'])
            ->withTrashed()
            ->first();
        if($item){
            $item ->makeHidden(['slug','meta','uuid']);
        }

        if(!$item)
        {
            $response['success'] = false;
            $response['errors'][] = 'Record not found with ID: '.$id;

            return $response;
        }
        $response['success'] = true;
        $response['data'] = $item;


        return $response;
        dd($response);

    }
    //-------------------------------------------------

    public static function updateItem($request, $id)
    {
        $inputs = $request->all();

        // Validate the inputs, passing the ID to ignore it in the uniqueness check
        $validation = self::validation($inputs, $id);
        if (!$validation['success']) {
            return $validation;
        }


        // Check if name already exists
        $item = self::where('id', '!=', $id)
            ->withTrashed()
            ->where('name', $inputs['name'])
            ->first();

        if ($item) {
            $error_message = "This name already exists" . ($item->deleted_at ? ' in trash.' : '.');
            return [
                'success' => false,
                'errors' => [$error_message]
            ];
        }

        // Retrieve the item to update
        $item = self::where('id', $id)
            ->withTrashed()
            ->first();

        if (!$item) {
            return [
                'success' => false,
                'errors' => ['Record not found with ID: ' . $id]
            ];
        }

        if (isset($inputs['shift_start_time']) && isset($inputs['shift_end_time'])) {
            $start_time = strtotime($inputs['shift_start_time']);
            $end_time = strtotime($inputs['shift_end_time']);

            if ($start_time >= $end_time) {
                return [
                    'success' => false,
                    'errors' => ["Shift start time must be earlier than shift end time."]
                ];
            }
        }

        // Check for changes in working hours
        $working_hours_changed = ($item->shift_start_time != $inputs['shift_start_time']) ||
            ($item->shift_end_time != $inputs['shift_end_time']);

        $item->fill($inputs);
        $item->save();

        // Handle appointment rescheduling if working hours changed
        if ($working_hours_changed) {
            $appointments = Appointment::where('doctor_id', $id)
                ->where('patient_id', '!=', null)
                ->get();

            foreach ($appointments as $appointment) {
                $subject = 'Appointment Rescheduled - Doctor Working Hours Changed';
                self::sendRescheduleMail($appointment, $subject);

                $appointment->status = 0; // Assuming 0 means cancelled
                $appointment->reason = 'Doctor changed their timings';
                $appointment->save();
            }
        }

        $response = self::getItem($item->id);
        $response['messages'][] = trans("vaahcms-general.saved_successfully");
        return $response;
    }


    public static function sendRescheduleMail($inputs, $subject)
    {
        $doctor = Doctor::find($inputs['doctor_id']);
        $patient = Patient::find($inputs['patient_id']);
        $timezone = Session::get('user_timezone');
        $date = Carbon::parse($inputs['date'])->toDateString();
        $slot_start_time = self::formatTime($inputs['slot_start_time'], $timezone);
        $slot_end_time = self::formatTime($inputs['slot_end_time'], $timezone);
        $appointment_url = vh_get_assets_base_url().'/backend/appointments#/appointments';

        $message_patient = sprintf(
            'Hello, %s,<br><br>Your appointment with Dr. %s on %s from %s to %s has been cancelled because the doctor is no longer available.<br><br>
    We apologize for the inconvenience.<br><br>
    <a href="%s" style="text-decoration:none;">
        <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            Book with Another Doctor
        </button>
    </a><br><br>Thank you.',
            $patient->name,
            $doctor->name,
            $date,
            $slot_start_time,
            $slot_end_time,
            $appointment_url
        );

// Send the email with HTML content
        VaahMail::dispatchGenericMail($subject, $message_patient, $patient->email);
    }

//------------------------------------------------------------
    public static function deleteItem($request, $id): array
    {
        dd('hello');
        $item = self::where('id', $id)->withTrashed()->first();
        if (!$item) {
            $response['success'] = false;
            $response['errors'][] = trans("vaahcms-general.record_does_not_exist");
            return $response;
        }
        $item->forceDelete();

        $response['success'] = true;
        $response['data'] = [];
        $response['messages'][] = trans("vaahcms-general.record_has_been_deleted");

        return $response;
    }


    //-------------------------------------------------
    private static function convertUtcToIst($timeString)
    {
        // Create a DateTime object from the UTC time string
        $utcDateTime = new DateTime($timeString, new DateTimeZone('UTC'));

        // Set the timezone to IST
        $utcDateTime->setTimezone(new DateTimeZone('Asia/Kolkata'));

        // Format the time into 12-hour format with AM/PM
        return $utcDateTime->format('h:i A'); // Example: 04:22 PM
    }
    //-------------------------------------------------

    public static function sendCancellationMail($inputs, $subject)
    {
        $doctor = Doctor::find($inputs['doctor_id']);
        $patient = Patient::find($inputs['patient_id']);
        $timezone = Session::get('user_timezone');
        $date = Carbon::parse($inputs['date'])->toDateString();
        $slot_start_time = self::formatTime($inputs['slot_start_time'], $timezone);
        $slot_end_time = self::formatTime($inputs['slot_end_time'], $timezone);
        $appointment_url = vh_get_assets_base_url().'/backend/appointments#/appointmentsbackend/appointments#/appointments';

        $message_patient = sprintf(
            'Hello, %s,<br><br>Your appointment with Dr. %s on %s has been cancelled because the doctor is no longer available.<br><br>
        We apologize for the inconvenience.<br><br>
        <a href="%s" style="text-decoration:none;">
            <button style="background-color: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Book with Another Doctor
            </button>
        </a><br><br>Thank you.',
            $patient->name,
            $doctor->name,
            $date,
            $appointment_url
        );

        // Send the email with HTML content
        VaahMail::dispatchGenericMail($subject, $message_patient, $patient->email);
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

    public static function validation($inputs, $id = null)
    {
        $rules = array(
            'name' => 'required|max:150',
            'specialization' => 'required|max:20',
            'email' => 'required|email|max:150|unique:doctor,email' . ($id ? ",$id" : ''),
            'phone' => 'required|max:11',
            'shift_start_time' => 'required|max:150',
            'shift_end_time' => 'required|max:150',
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



        $inputs['name'] = $faker->name;
        $inputs['specialization'] = $faker->randomElement(['Cardiology', 'Dermatology', 'Pediatrics', 'Neurology', 'Orthopedics']);

        // Override the 'phone' field to start with 9, 8, or 7
        $inputs['phone'] = $faker->numerify($faker->randomElement(['9#########', '8#########', '7#########']));

        // Override the 'shift_start_time' and 'shift_end_time' with valid times
        $startHour = $faker->randomElement(['11:00 AM', '2:00 PM']);
        $inputs['shift_start_time'] = $startHour;
        $inputs['shift_end_time'] = date('h:i A', strtotime("+4 hours", strtotime($startHour))); // 4 hours later

        $priceOptions = range(500, 1000, 100);
        $inputs['price'] = $faker->randomElement($priceOptions);


        $inputs['is_active'] = 1;

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
