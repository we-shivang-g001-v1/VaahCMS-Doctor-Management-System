<script setup>
import { vaah } from '../../../vaahvue/pinia/vaah'
import { useDoctorStore } from '../../../stores/store-doctors'
import { ref, watch } from 'vue';
const store = useDoctorStore();
const useVaah = vaah();


const visibleRight = ref(false) // Control sidebar visibility
const currentAppointmentCount = ref(0); // Store the current appointment count
const appointmentDetails = ref([]);// Store the current appointment count
const bookedAppointments = ref([]); // Store booked appointments
const cancelledAppointments = ref([]); // Store cancelled appointments

function openSidebar(appointmentsCount, appointmentsList) {
    currentAppointmentCount.value = appointmentsCount; // Set the count to be displayed
    appointmentDetails.value = appointmentsList;

    // Filter based on status
    bookedAppointments.value = appointmentsList.filter(appointment => appointment.status === 1);
    cancelledAppointments.value = appointmentsList.filter(appointment => appointment.status === 0);

    visibleRight.value = true; // Show the sidebar
}

function convertUtcToIst(utcTimeString) {
    // Split the time string into hours, minutes, and seconds
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

console.log(store.action.items)


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
.custom-sidebar {
    width: 1000px; /* Adjust the width as needed */
}

.custom-sidebar .p-sidebar-content {
    padding: 20px; /* Add padding for better spacing */
}


.styled-table {
    border-collapse: collapse;
    width: 100%; /* Table takes full width of its container */
}

.styled-table th,
.styled-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

.error-container {
    display: flex;
    justify-content: space-between;
    gap: 20px; /* Add gap between the columns */
}

.error-column {
    width: 48%; /* Default width for each column when both are present */
}

.full-width {
    width: 100%; /* If only one column is present, take full width */
}

.styled-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

@media screen and (max-width: 575px) {
    .error-container {
        flex-direction: column; /* Stack columns vertically on small screens */
    }

    .error-column {
        width: 100%; /* Full width for each column on small screens */
    }
}

/* Add your additional styles here */
</style>

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

            <Column field="slug" header="Name"
                    class="overflow-wrap-anywhere"
                    :sortable="true">

                <template #body="prop">
                    <Badge v-if="prop.data.deleted_at"
                           value="Trashed"
                           severity="danger"></Badge>
                    {{prop.data.name}}

                </template>

            </Column>
             <Column field="totalAppointment" header="Appointments"
                     v-if="store.isViewLarge()"
                     class="overflow-wrap-anywhere"
                     :sortable="true">
                 <template #body="prop">
                     <div style="display:flex; justify-content:center; align-items:center;">
                         <Badge v-if="prop.data.appointments_count > 0"
                                :style="{ backgroundColor: '#4CAF50', color: 'white' }"
                                @click="openSidebar(prop.data.appointments_count, prop.data.appointments_list)"
                                style="cursor: pointer;"> <!-- Make it look clickable -->
                             {{ prop.data.appointments_count }}
                         </Badge>
                         <span v-else style="color: gray;">N/A</span>
                     </div>
                 </template>
             </Column>
             <Column field="email" header="Email"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.email}}
                 </template>

             </Column>
             <Column field="specialization" header="Specialization"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.specialization}}
                 </template>

             </Column>
             <Column field="phone" header="Phone"
                     v-if="store.isViewLarge()"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{prop.data.phone}}
                 </template>

             </Column>
             <Column field="price" header="Price per Appointment"
                     class="overflow-wrap-anywhere text-center"
                     :sortable="true">
             <template #body="prop">
                 <div class="text-center">  <!-- Added div for centering -->
                     {{ prop.data.price }}
                 </div>
             </template>

             </Column>
             <Column field="shift_start_time" header="Start Time"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">
                     {{ formatTimeWithAmPm(convertUtcToIst(prop.data.shift_start_time)) }}
<!--                     {{prop.data.shift_start_time}}-->
                 </template>

             </Column>

             <Column field="shift_end_time" header="End Time"
                     class="overflow-wrap-anywhere"
                     :sortable="true">

                 <template #body="prop">

                     {{ formatTimeWithAmPm(convertUtcToIst(prop.data.shift_end_time)) }}
<!--                     {{prop.data.shift_end_time}}-->

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

            <Column field="is_active" v-if="store.isViewLarge()"
                    :sortable="true"
                    style="width:100px;"
                    header="Is Active">

                <template #body="prop">
                    <InputSwitch v-model.bool="prop.data.is_active"
                                 data-testid="doctors-table-is-active"
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
                                data-testid="doctors-table-to-view"
                                v-tooltip.top="'View'"
                                @click="store.toView(prop.data)"
                                icon="pi pi-eye" />

                        <Button class="p-button-tiny p-button-text"
                                data-testid="doctors-table-to-edit"
                                v-tooltip.top="'Update'"
                                @click="store.toEdit(prop.data)"
                                icon="pi pi-pencil" />

                        <Button class="p-button-tiny p-button-danger p-button-text"
                                data-testid="doctors-table-action-trash"
                                v-if="store.isViewLarge() && !prop.data.deleted_at && store.hasPermission(store.assets.permission, 'appointments-has-access-of-patient')"
                                @click="store.itemAction('trash', prop.data)"
                                v-tooltip.top="'Trash'"
                                icon="pi pi-trash" />


                        <Button class="p-button-tiny p-button-success p-button-text"
                                data-testid="doctors-table-action-restore"
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
        <!-- Sidebar Component -->
        <Sidebar v-model:visible="visibleRight" header="Appointments Details" position="right" class="custom-sidebar">
            <p>Total Appointments: {{ currentAppointmentCount }}</p>

            <TabView>
                <!-- Booked Tab -->
                <TabPanel header="Booked">
                    <DataTable :value="bookedAppointments" dataKey="id" class="p-datatable-sm p-datatable-hoverable-rows" emptyMessage="No records available">
                        <Column field="id" header="ID" :sortable="true" :style="{ width: '80px' }">
                            <template #body="prop">
                                {{ prop.data.id }}
                            </template>
                        </Column>

                        <Column field="patient.name" header="Patient Name" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.patient.name }} <!-- Accessing nested patient name -->
                            </template>
                        </Column>

                        <Column field="date" header="Appointment Date" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.date }} <!-- Displaying the appointment date -->
                            </template>
                        </Column>

                        <Column field="slot_start_time" header="Start Time" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ formatTimeWithAmPm(convertUtcToIst(prop.data.slot_start_time)) }}<!-- Displaying the start time -->
                            </template>
                        </Column>

                        <Column field="slot_end_time" header="End Time" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ formatTimeWithAmPm(convertUtcToIst(prop.data.slot_end_time)) }}<!-- Displaying the end time -->
                            </template>
                        </Column>

                        <Column field="reason" header="Reason" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.reason || 'N/A' }} <!-- Displaying reason or 'N/A' if not present -->
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>

                <!-- Cancelled Tab -->
                <TabPanel header="Cancelled">
                    <DataTable :value="cancelledAppointments" dataKey="id" class="p-datatable-sm p-datatable-hoverable-rows" emptyMessage="No records available">
                        <Column field="id" header="ID" :sortable="true" :style="{ width: '80px' }">
                            <template #body="prop">
                                {{ prop.data.id }}
                            </template>
                        </Column>

                        <Column field="patient.name" header="Patient Name" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.patient.name }} <!-- Accessing nested patient name -->
                            </template>
                        </Column>

                        <Column field="date" header="Appointment Date" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.date }}
                            </template>
                        </Column>

                        <Column field="slot_start_time" header="Start Time" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ formatTimeWithAmPm(convertUtcToIst(prop.data.slot_start_time)) }}
                            </template>
                        </Column>

                        <Column field="slot_end_time" header="End Time" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ formatTimeWithAmPm(convertUtcToIst(prop.data.slot_end_time)) }}
                            </template>
                        </Column>

                        <Column field="reason" header="Reason" :sortable="true" class="overflow-wrap-anywhere">
                            <template #body="prop">
                                {{ prop.data.reason || 'N/A' }}
                            </template>
                        </Column>
                    </DataTable>
                </TabPanel>
            </TabView>

        </Sidebar>

        <template>
            <Dialog
                v-model:visible="store.is_visible_errors"
                maximizable
                modal
                header="Error Messages"
                :style="{ width: '50rem' }"
                :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            >
                <div class="error-container">
                    <!-- Phone Errors Section -->
                    <div
                        class="error-column"
                        v-if="store.data_res_phone && store.data_res_phone.length > 0"
                        :class="{ 'full-width': !store.data_res_email || store.data_res_email.length === 0 }">
                        <table class="styled-table">
                            <thead>
                            <tr>
                                <th>Phone Error Messages</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(phoneError, index) in store.data_res_phone" :key="'phone-'+index">
                                <td>{{ phoneError }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Email Errors Section -->
                    <div
                        class="error-column"
                        v-if="store.data_res_email && store.data_res_email.length > 0"
                        :class="{ 'full-width': !store.data_res_phone || store.data_res_phone.length === 0 }">
                        <table class="styled-table">
                            <thead>
                            <tr>
                                <th>Email Error Messages</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(emailError, index) in store.data_res_email" :key="'email-'+index">
                                <td>{{ emailError }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </Dialog>
        </template>




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
