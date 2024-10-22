<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAppointmentStore } from '../../../stores/store-appointments'

const store = useAppointmentStore();
const useVaah = vaah();

function convertUtcToIst(utcTimeString) {
    let [hours, minutes, seconds] = utcTimeString.split(':').map(Number);

    // Add 5 hours and 30 minutes to convert UTC to IST
    hours += 5;
    minutes += 30;

    // Handle overflow for minutes
    if (minutes >= 60) {
        minutes -= 60;
        hours += 1;
    }

    // Handle overflow for hours (24-hour format)
    if (hours >= 24) {
        hours -= 24;
    }

    // Format hours, minutes, and seconds with leading zeros if needed
    const hoursStr = hours.toString().padStart(2, '0');
    const minutesStr = minutes.toString().padStart(2, '0');
    const secondsStr = seconds.toString().padStart(2, '0');

    return `${hoursStr}:${minutesStr}:${secondsStr}`;
}


function formatTimeWithAmPm(time) {
    if (!time) return '';

    const [hours, minutes] = time.split(':');
    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes);
    const amPm = date.getHours() >= 12 ? 'PM' : 'AM';

    let hour = date.getHours() % 12;
    if (hour === 0) hour = 12;

    return `${hour}:${minutes} ${amPm}`;
}


</script>
<style>
.green-reason {
    color: green;
}

.red-reason {
    color: red;
}

.na-reason {
    color: gray;
}

.blue-reason {
    color: blue;
}</style>

<template>

    <div v-if="store.list">
        <!--table-->
        {{store.is_visible_errors}}
         <DataTable :value="store.list.data"
                   dataKey="id"
                   :rowClass="store.setRowClass"
                   class="p-datatable-sm p-datatable-hoverable-rows"
                   :nullSortOrder="-1"
                   v-model:selection="store.action.items"
                   stripedRows
                   responsiveLayout="scroll">

            <Column selectionMode="multiple"
                    v-if="store.isViewLarge()"
                    headerStyle="width: 3em">
            </Column>

            <Column field="id" header="ID" :style="{width: '80px'}" :sortable="true">
            </Column>

             <Column field="patient" header="Patient Name"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.patient.name}}
                 </template>

             </Column>
             <Column field="doctor" header="Doctor Name"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{ prop.data.doctor && prop.data.doctor.name ? prop.data.doctor.name : 'N/A' }}
                 </template>

             </Column>
             <Column field="status" header="Status" class="overflow-wrap-anywhere" :sortable="true">
                 <template #body="prop">
        <span :style="{ color: prop.data.status === 1 ? 'green' : 'red' }">
            {{ prop.data.status === null ? 'null' : (prop.data.status === 1 ? 'Booked' : 'Cancel') }}
        </span>
                 </template>
             </Column>
             <Column field="date" header="Date and Slot"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data?.date}} at {{ formatTimeWithAmPm(convertUtcToIst(prop.data.slot_start_time)) }} - {{ formatTimeWithAmPm(convertUtcToIst(prop.data.slot_end_time)) }}
                 </template>

             </Column>


                <Column field="updated_at" header="Updated"
                        v-if="store.isViewLarge()"
                        style="width:150px;"
                        :sortable="true">

                    <template #body="prop">
                        {{ useVaah.strToSlug(prop.data.updated_at.split(' ')[0]) }}
                        {{ useVaah.strToSlug(formatTimeWithAmPm(prop.data.updated_at.split(' ')[1])) }}
                    </template>

                </Column>
             <Column field="reason" header="Cancellation Reason"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
    <span :class="{
        'green-reason': prop.data.reason === 'Time Updated by Patient',
        'red-reason': prop.data.reason === 'Doctor Change Their Timimgs',
        'na-reason': prop.data.reason === null || prop.data.reason === 'NA',
        'blue-reason': !['Time Updated by Patient', 'Doctor Change Their Timimgs', null, 'NA'].includes(prop.data.reason)
    }">
    {{ prop.data.reason !== null && prop.data.reason !== 'NA' ? prop.data.reason : 'NA' }}
</span>
                 </template>


             </Column>

            <Column field="is_active" v-if="store.isViewLarge()"
                    :sortable="true"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="appointments-table-is-active"
                                 v-bind:false-value="0"  v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 @input="store.toggleIsActive(prop.data)">
                    </InputSwitch>
                </template>

            </Column>

            <Column field="actions" style="width:150px;"
                    :style="{width: store.getActionWidth() }"
                    :header="store.getActionLabel()">

                <template #body="prop">
                    <div class="p-inputgroup ">

                        <Button class="p-button-tiny p-button-text"
                                data-testid="appointments-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button  v-if="store.hasPermission(store.assets.permission, 'appointments-has-access-of-patient')" class="p-button-tiny p-button-text"
                            data-testid="appointments-table-to-edit"
                            v-tooltip.top="'Update'"
                            @click="store.toEdit(prop.data)"
                            icon="pi pi-pencil"
                           />

                        <Button  class="p-button-tiny p-button-danger p-button-text"
                                data-testid="appointments-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at && store.hasPermission(store.assets.permission, 'appointments-has-access-of-patient') && store.hasPermission(store.assets.permission, 'appointments-has-access-of-doctor-section')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="appoinments-table-action-trash"
                                v-if="store.isViewLarge()&& prop.data.status !== 0 && !prop.data.deleted_at"
                                @click="store.confirmToCancelAppointment( prop.data)"
                                v-tooltip.top="'Cancel Appointment'"
                                icon="pi pi-times" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="appointments-table-action-restore"
                                v-if="store.isViewLarge() && prop.data.deleted_at"
                                @click="store.itemAction('restore', prop.data)"
                                v-tooltip.top="'Restore'"
                                icon="pi pi-replay" />


                    </div>

                </template>


            </Column>

             <template #empty>
                 <div class="text-center py-3">
                     No records found.
                 </div>
             </template>

        </DataTable>
        <!--/table-->

        <Dialog
            v-model:visible="store.is_visible_errors"
            maximizable
            modal
            header="Error Messages"
            :style="{ width: '50rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >

            <div
                class="error-column"
                v-if="store.import_errors && store.import_errors.length > 0"
                :class="'full-width'">
                <table class="styled-table">
                    <thead>
                    <tr>
                        <th>Appointment Error Messages</th>

                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(appointmentError, index) in store.import_errors" :key="'appointment-'+index">
                        <td>{{ appointmentError }}</td>
                    </tr>
                    </tbody>

                </table>
            </div>
        </Dialog>
        <!--paginator-->
        <Paginator v-if="store.query.rows"
                   v-model:rows="store.query.rows"
                   :totalRecords="store.list.total"
                   :first="((store.query.page??1)-1)*store.query.rows"
                   @page="store.paginate($event)"
                   :rowsPerPageOptions="store.rows_per_page"
                   class="bg-white-alpha-0 pt-2">
        </Paginator>
        <!--/paginator-->

    </div>

</template>
