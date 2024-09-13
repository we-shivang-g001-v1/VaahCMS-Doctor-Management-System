<?php

return [
    "name"=> "Appointments",
    "title"=> "Doctor Appointments",
    "slug"=> "appointments",
    "thumbnail"=> "https://img.site/p/300/160",
    "is_dev" => env('MODULE_APPOINTMENTS_ENV')?true:false,
    "excerpt"=> "Doctor Appointment System",
    "description"=> "Doctor Appointment System",
    "download_link"=> "",
    "author_name"=> "vaah",
    "author_website"=> "https://vaah.dev",
    "version"=> "0.0.1",
    "is_migratable"=> true,
    "is_sample_data_available"=> true,
    "db_table_prefix"=> "vh_appointments_",
    "providers"=> [
        "\\VaahCms\\Modules\\Appointments\\Providers\\AppointmentsServiceProvider"
    ],
    "aside-menu-order"=> null
];
