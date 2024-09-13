<?php
use VaahCms\Modules\Appointments\Http\Controllers\Backend\doctorsController;
/*
 * API url will be: <base-url>/public/api/appointments/doctors
 */
Route::group(
    [
        'prefix' => 'appointments/doctors',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [doctorsController::class, 'getAssets'])
        ->name('vh.backend.appointments.api.doctors.assets');
    /**
     * Get List
     */
    Route::get('/', [doctorsController::class, 'getList'])
        ->name('vh.backend.appointments.api.doctors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [doctorsController::class, 'updateList'])
        ->name('vh.backend.appointments.api.doctors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [doctorsController::class, 'deleteList'])
        ->name('vh.backend.appointments.api.doctors.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [doctorsController::class, 'createItem'])
        ->name('vh.backend.appointments.api.doctors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [doctorsController::class, 'getItem'])
        ->name('vh.backend.appointments.api.doctors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [doctorsController::class, 'updateItem'])
        ->name('vh.backend.appointments.api.doctors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [doctorsController::class, 'deleteItem'])
        ->name('vh.backend.appointments.api.doctors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [doctorsController::class, 'listAction'])
        ->name('vh.backend.appointments.api.doctors.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [doctorsController::class, 'itemAction'])
        ->name('vh.backend.appointments.api.doctors.item.action');



});
