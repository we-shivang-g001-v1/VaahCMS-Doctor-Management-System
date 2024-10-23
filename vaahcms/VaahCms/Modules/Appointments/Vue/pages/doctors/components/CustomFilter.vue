<script  setup>

import { useDoctorStore } from '../../../stores/store-doctors'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'
import { watch,ref } from 'vue';
const store = useDoctorStore();
const shiftTimings = [
    { value: '05:00:00-09:00:00', label: '05:00 PM - 09:00 PM' },
    { value: '09:00:00-13:00:00', label: '09:00 AM - 01:00 PM' },
    { value: '13:00:00-17:00:00', label: '01:00 PM - 05:00 PM' },
    { value: '17:00:00-21:00:00', label: '05:00 PM - 09:00 PM' },
    { value: '21:00:00-23:00:00', label: '09:00 PM - 11:00 PM' },
];
// Ensure initial values are set
const priceRange = ref([500, 1000]);
const minPrice = 500; // Define minimum price
const maxPrice = 1000; // Define maximum price

// Watch for changes to the price range
watch(priceRange, (newValue) => {
    // Validate the new price range
    if (newValue[0] < minPrice || newValue[0] >= newValue[1] || newValue[1] > maxPrice) {
        return; // Exit if the range is invalid
    }
    // Update the store with the valid price range
    store.query.filter.price = `${newValue[0]}-${newValue[1]}`;
});
// Optionally, you can create a method to reset the price range
const resetPriceRange = () => {
    priceRange.value = [minPrice, maxPrice];
};
const shiftStartTime = ref(null);
const shiftEndTime = ref(null);

// Watch for time changes and update the store
watch([shiftStartTime, shiftEndTime], ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        store.query.filter.shift_time = `${formatTime(newStart)}-${formatTime(newEnd)}`;
    }
});

// Function to format time as HH:mm:ss
const formatTime = (date) => {
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

</script>

<template>
    <div class="col-3" v-if="store.show_custom_filters">

        <Panel class="is-small">

            <template class="p-1" #header>

                <div class="flex flex-row">
                    <div >
                        <b class="mr-1">Custom Filters</b>
                    </div>

                </div>

            </template>

            <template #icons>

                <div class="p-inputgroup">

                    <Button data-testid="doctors-hide-filter"
                            class="p-button-sm"
                            @click="store.show_custom_filters = false">
                        <i class="pi pi-times"></i>
                    </Button>

                </div>

            </template>



            <VhFieldVertical>
                <template #label>
                    <b>Specialization:</b>
                </template>


                <div class="field-radiobutton">
                    <Checkbox name="specialization-physician"
                                 inputId="specialization-physician"
                                 data-testid="doctors-filters-specialization-physician"
                                 value="Physician"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-physician" class="cursor-pointer">Physician</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-ortho"
                                 inputId="specialization-ortho"
                                 data-testid="doctors-filters-specialization-ortho"
                                 value="Ortho"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-ortho" class="cursor-pointer">ortho</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-dentist"
                                 inputId="specialization-dentist"
                                 data-testid="doctors-filters-specialization-dentist"
                                 value="Dentist"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-dentist" class="cursor-pointer">Dentist</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-pediatrics"
                                 inputId="specialization-pediatrics"
                                 data-testid="doctors-filters-specialization-pediatrics"
                                 value="Pediatrics"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-pediatrics" class="cursor-pointer">Pediatrics</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-neurology"
                                 inputId="specialization-neurology"
                                 data-testid="doctors-filters-specialization-neurology"
                                 value="Neurology"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-neurology" class="cursor-pointer">Neurology</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-orthopedics"
                                 inputId="specialization-orthopedics"
                                 data-testid="doctors-filters-specialization-orthopedics"
                                 value="Orthopedics"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-orthopedics" class="cursor-pointer">Orthopedics</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-cardiology"
                                 inputId="specialization-cardiology"
                                 data-testid="doctors-filters-specialization-cardiology"
                                 value="Cardiology"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-cardiology" class="cursor-pointer">Cardiology</label>
                </div>

                <div class="field-radiobutton">
                    <Checkbox name="specialization-dermatology"
                                 inputId="specialization-dermatology"
                                 data-testid="doctors-filters-specialization-dermatology"
                                 value="Dermatology"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-dermatology" class="cursor-pointer">Dermatology</label>
                </div>

            </VhFieldVertical>

            <Divider/>


                <VhFieldVertical>
                    <template #label>
                        <b class="price-label">Price Range:</b>
                    </template>

                    <Slider v-model="priceRange"
                            class="w-56 slider"
                            :range="true"
                            :min="minPrice"
                            :max="maxPrice"
                            :step="1"
                            :tooltip="true"
                            :style="sliderStyle"/>

                    <div class="selected-price-range">
                        <b>Selected Price Range:</b>
                        <span class="range-values">{{ priceRange[0] }} - {{ priceRange[1] }}</span>
                    </div>
                </VhFieldVertical>




            <Divider/>

            <VhFieldVertical>
                <template #label>
                    <b>Select Shift Timings:</b>
                </template>

                <div class="flex flex-col gap-2">
                    <div>
                        <label for="shiftStartTime" class="font-semibold">Shift Start Time:</label>
                        <Calendar
                            v-model="shiftStartTime"
                            :hourFormat="'12'"
                            :pt="{
                    monthPicker: {class: 'w-15rem'},
                    yearPicker: {class: 'w-15rem'}
                }"
                            time-only
                            placeholder="Shift Start Time"
                            @change="updateShiftTime"
                        />
                    </div>

                    <div>
                        <label for="shiftEndTime" class="font-semibold">Shift End Time:</label>
                        <Calendar
                            v-model="shiftEndTime"
                            :hourFormat="'12'"
                            :pt="{
                    monthPicker: {class: 'w-15rem'},
                    yearPicker: {class: 'w-15rem'}
                }"
                            time-only
                            placeholder="Shift End Time"
                            @change="updateShiftTime"
                        />
                    </div>
                </div>

            </VhFieldVertical>
            <Divider/>

        </Panel>

    </div>
</template>

<style scoped>


.price-label {
    font-size: 1.2em; /* Slightly larger font */
    color: #333; /* Dark color for contrast */
}

.slider {
    margin: 10px 0; /* Space around slider */
}

.selected-price-range {
    margin-top: 10px; /* Space above selected range */
    font-size: 1.1em; /* Slightly larger font for readability */
    color: #666; /* Lighter text color */
}

.range-values {
    font-weight: bold; /* Bold for emphasis */
    color: #007bff; /* Primary color for values */
}
</style>
