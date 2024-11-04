<script setup>
import { useDoctorStore } from '../../../stores/store-doctors';
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue';
import { watch, ref } from 'vue';

const store = useDoctorStore();
const shiftTimings = [
    { value: '00:00:00-03:30:00', label: '05:00 AM - 09:00 AM' },
    { value: '03:31:00-07:30:00', label: '09:00 AM - 01:00 PM' },
    { value: '07:31:00-11:30:00', label: '01:00 PM - 05:00 PM' },
    { value: '11:31:00-15:30:00', label: '05:00 PM - 09:00 PM' },
    { value: '15:31:00-17:30:00', label: '09:00 PM - 11:00 PM' },
];

// Ensure initial values are set
const minPrice = ref(store.minimumPrice !== null ? store.minimumPrice : 500); // Use store's minimum price
const maxPrice = ref(store.highestPrice !== null ? store.highestPrice : 1000); // Use store's highest price
const priceRange = ref([minPrice.value, maxPrice.value]); // Initialize price range with reactive values
const showSlider = ref(maxPrice.value !== null); // Show slider if highestPrice is not null

// Watch for changes to highestPrice
watch(
    () => store.highestPrice,
    (newValue) => {
        if (newValue !== null) {
            maxPrice.value = newValue; // Update maxPrice when highestPrice changes
            priceRange.value[1] = newValue; // Set the upper bound of the price range to the new highest price
            showSlider.value = newValue !== null; // Show slider if highestPrice is not null
        }
    }
);

// Watch for changes to minimumPrice
watch(
    () => store.minimumPrice,
    (newValue) => {
        if (newValue !== null) {
            minPrice.value = newValue; // Update minPrice when minimumPrice changes
            priceRange.value[0] = newValue;
        }
    }
);

watch(priceRange, (newValue) => {
    // Validate the new price range
    if (newValue[0] < minPrice.value || newValue[0] >= newValue[1] || newValue[1] > maxPrice.value) {
        return; // Exit if the range is invalid
    }
    // Update the store with the valid price range
    store.query.filter.price = `${newValue[0]}-${newValue[1]}`;
});

// Optionally, you can create a method to reset the price range
const resetPriceRange = () => {
    priceRange.value = [minPrice.value, maxPrice.value];
};

const shiftStartTime = ref(null);
const shiftEndTime = ref(null);

// Watch for time changes and update the store
watch([shiftStartTime, shiftEndTime], ([newStart, newEnd]) => {
    if (newStart && newEnd) {
        const utcStart = convertToUTC(newStart);
        const utcEnd = convertToUTC(newEnd);
        store.query.filter.shift_time = `${utcStart}-${utcEnd}`;
    }
});

// Function to convert local time to UTC as HH:mm:ss
const convertToUTC = (date) => {
    const utcDate = new Date(date.getTime() + date.getTimezoneOffset() * 60000);
    return utcDate.toISOString().substr(11, 8); // Extract HH:mm:ss from ISO string
};

</script>

<template>
    <div class="col-3" v-if="store.show_custom_filters">
        <Panel class="is-small">
            <template class="p-1" #header>
                <div class="flex flex-row">
                    <div>
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

                <div v-for="specialization in store.specializations" :key="specialization.name" class="field-radiobutton">
                    <Checkbox
                        :name="`specialization-${specialization.name}`"
                        :inputId="`specialization-${specialization.name}`"
                        :data-testid="`doctors-filters-specialization-${specialization.name}`"
                        :value="specialization.name"
                        v-model="store.query.filter.specialization"
                    />
                    <label :for="`specialization-${specialization.name}`" class="cursor-pointer">
                        {{ specialization.name }} ({{ specialization.doctor_count }})
                    </label>
                </div>
            </VhFieldVertical>

            <Divider/>

            <!-- Price Range Slider -->
            <VhFieldVertical v-if="showSlider">
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
                <div v-for="(shift, index) in shiftTimings" :key="index" class="field-radiobutton">
                    <RadioButton
                        :name="'shift-time-' + (index + 1)"
                        :inputId="'shift-time-' + (index + 1)"
                        :data-testid="'doctors-filters-shift-time-' + (index + 1)"
                        :value="shift.value"
                        v-model="store.query.filter.shift_time"
                    />
                    <label :for="'shift-time-' + (index + 1)" class="cursor-pointer">{{ shift.label }}</label>
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
