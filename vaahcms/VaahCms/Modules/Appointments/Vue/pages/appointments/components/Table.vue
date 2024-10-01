<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useAppointmentStore } from '../../../stores/store-appointments'

const store = useAppointmentStore();
const useVaah = vaah();

function convertUTCtoIST(utcTimeString) {
    if (!utcTimeString) return '';
    const [utcHours, utcMinutes, utcSeconds] = utcTimeString.split(':').map(Number);
    const utcDate = new Date(Date.UTC(1970, 0, 1, utcHours, utcMinutes, utcSeconds));
    const istOffset = 5.5 * 60 * 60 * 1000;
    const istDate = new Date(utcDate.getTime() + istOffset);
    return istDate.toLocaleTimeString('en-IN', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false, // 24-hour format
    });
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

    // Corrected template literal
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
                     {{prop.data.doctor.name}}
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
                     {{prop.data?.date}} at {{ formatTimeWithAmPm(prop.data.slot_start_time) }} - {{ formatTimeWithAmPm(prop.data.slot_end_time) }}
                 </template>

             </Column>


                <Column field="updated_at" header="Updated"
                        v-if="store.isViewLarge()"
                        style="width:150px;"
                        :sortable="true">

                    <template #body="prop">
                        {{useVaah.strToSlug(prop.data.updated_at)}}
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
