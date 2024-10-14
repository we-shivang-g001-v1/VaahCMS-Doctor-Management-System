<script setup>
import {onMounted, ref, watch, computed} from "vue";
import { useAppointmentStore } from '../../stores/store-appointments'

import VhField from './../../vaahvue/vue-three/primeflex/VhField.vue'
import {useRoute} from 'vue-router';

const  isValidTime = (date) => date instanceof Date && !isNaN (date.getTime())
const store = useAppointmentStore();
const route = useRoute();

onMounted(async () => {
    /**
     * Fetch the record from the database
     */
    if((!store.item || Object.keys(store.item).length < 1)
        && route.params && route.params.id)
    {
        await store.getItem(route.params.id);
    }

    await store.getFormMenu();
});

//--------form_menu
const form_menu = ref();
const toggleFormMenu = (event) => {
    form_menu.value.toggle(event);
};




//--------/form_menu
// Fetch the selected doctor's full details
const selectedDoctor = computed(() => {
    return store.assets.doctor.find(doctor => doctor.id === store.item.doctor_id) || {};
});

onMounted(async () => {
    if ((!store.item || Object.keys(store.item).length < 1) && route.params && route.params.id) {
        await store.getItem(route.params.id);
    }
    await store.getFormMenu();
});

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
<template>

    <div class="col-6" >

        <Panel class="is-small">

            <template class="p-1" #header>


                <div class="flex flex-row">
                    <div class="p-panel-title">
                        <span v-if="store.item && store.item.id">
                            Update
                        </span>
                        <span v-else>
                            Create
                        </span>
                    </div>

                </div>


            </template>

            <template #icons>


                <div class="p-inputgroup">

                    <Button class="p-button-sm"
                            v-tooltip.left="'View'"
                            v-if="store.item && store.item.id"
                            data-testid="appointments-view_item"
                            @click="store.toView(store.item)"
                            icon="pi pi-eye"/>

                    <Button label="Save"
                            class="p-button-sm"
                            v-if="store.item && store.item.id"
                            data-testid="appointments-save"
                            @click="store.itemAction('save')"
                            icon="pi pi-save"/>

                    <Button label="Create & New"
                            v-else
                            @click="store.itemAction('create-and-new')"
                            class="p-button-sm"
                            data-testid="appointments-create-and-new"
                            icon="pi pi-save"/>


                    <!--form_menu-->
                    <Button
                        type="button"
                        @click="toggleFormMenu"
                        class="p-button-sm"
                        data-testid="appointments-form-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="form_menu"
                          :model="store.form_menu_list"
                          :popup="true" />
                    <!--/form_menu-->


                    <Button class="p-button-primary p-button-sm"
                            icon="pi pi-times"
                            data-testid="appointments-to-list"
                            @click="store.toList()">
                    </Button>
                </div>



            </template>


            <div v-if="store.item" class="mt-2">

                <Message severity="error"
                         class="p-container-message mb-3"
                         :closable="false"
                         icon="pi pi-trash"
                         v-if="store.item.deleted_at">

                    <div class="flex align-items-center justify-content-between">

                        <div class="">
                            Deleted {{store.item.deleted_at}}
                        </div>

                        <div class="ml-3">
                            <Button label="Restore"
                                    class="p-button-sm"
                                    data-testid="articles-item-restore"
                                    @click="store.itemAction('restore')">
                            </Button>
                        </div>

                    </div>

                </Message>


                <VhField label="Select Patient">
                    <Dropdown
                        filter
                        name="items-patient"
                        data-testid="items-patient"
                        placeholder="Select Patient"
                        :options="store.assets.patients"
                        v-model="store.item.patient_id"
                        option-label="name"
                        class="w-full"
                        showClear
                        option-value="id"
                    />
                </VhField>
                <VhField label="Select Doctor">
                    <Dropdown
                        filter
                        name="items-doctor"
                        data-testid="items-doctor"
                        placeholder="Select Doctor"
                        :options="store.assets.doctor && store.assets.doctor.length ? store.assets.doctor : []"
                        v-model="store.item.doctor_id"
                        option-label="name"
                        option-value="id"
                        class="w-full"
                        showClear
                    />
                </VhField>

                <VhField label="Doctor's Information" v-if="store.item.doctor_id && selectedDoctor && Object.keys(selectedDoctor).length">
                    <b>Email:</b> {{ selectedDoctor.email }}<br>
                    <b>Phone:</b> {{ selectedDoctor.phone }}<br>
                    <b>Specialization:</b> {{ selectedDoctor.specialization }}<br>
                    <b>Shift Time:</b>
                    {{ formatTimeWithAmPm(convertUtcToIst(selectedDoctor.shift_start_time)) }} -
                    {{ formatTimeWithAmPm(convertUtcToIst(selectedDoctor.shift_end_time)) }}<br>
                    (Please Select the time in the given time slot).
                </VhField>

                <VhField label="Date and Time" required>
                    <div class="p-inputgroup">
                        <Calendar
                            name="items-date"
                            date-format="yy-mm-dd"
                            :showIcon="true"
                            :minDate="today_date"
                            data-testid="items-date"
                            @date-select="handleDateChange($event,'date')"
                            v-model="store.item.date "
                            :pt="{
                                  monthPicker:{class:'w-15rem'},
                                  yearPicker:{class:'w-15rem'}
                              }"
                            placeholder="Select Appointment Date"
                        />
                        <Calendar
                            v-model="store.item.slot_start_time"
                            showTime hourFormat="12"
                            stepMinute="30"
                            :pt="{
                                  monthPicker:{class:'w-15rem'},
                                  yearPicker:{class:'w-15rem'}
                              }"
                            time-only
                            placeholder="Appointment Start Time"
                        />
                        <Calendar
                            v-model="store.item.slot_end_time"
                            showTime hourFormat="12"
                            stepMinute="30"
                            store.item.slot_start_time
                            :pt="{
                                  monthPicker:{class:'w-15rem'},
                                  yearPicker:{class:'w-15rem'}
                              }"
                            time-only
                            placeholder="Appointment End Time"
                        />
                    </div>
                </VhField>




                <VhField label="Is Active">
                    <InputSwitch v-bind:false-value="0"
                                 v-bind:true-value="1"
                                 class="p-inputswitch-sm"
                                 name="appointments-active"
                                 data-testid="appointments-active"
                                 v-model="store.item.is_active"/>
                </VhField>

            </div>
        </Panel>

    </div>

</template>
