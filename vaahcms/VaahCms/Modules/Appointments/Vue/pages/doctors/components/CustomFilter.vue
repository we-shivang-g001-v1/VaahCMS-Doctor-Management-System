<script  setup>

import { useDoctorStore } from '../../../stores/store-doctors'
import VhFieldVertical from './../../../vaahvue/vue-three/primeflex/VhFieldVertical.vue'

const store = useDoctorStore();
const shiftTimings = [
    { value: '05:00:00-09:00:00', label: '05:00 PM - 09:00 PM' },
    { value: '09:00:00-13:00:00', label: '09:00 AM - 01:00 PM' },
    { value: '13:00:00-17:00:00', label: '01:00 PM - 05:00 PM' },
    { value: '17:00:00-21:00:00', label: '05:00 PM - 09:00 PM' },
    { value: '21:00:00-23:00:00', label: '09:00 PM - 11:00 PM' },
];
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
                    <RadioButton name="specialization-physician"
                                 inputId="specialization-physician"
                                 data-testid="doctors-filters-specialization-physician"
                                 value="Physician"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-physician" class="cursor-pointer">Physician</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="specialization-ortho"
                                 inputId="specialization-ortho"
                                 data-testid="doctors-filters-specialization-ortho"
                                 value="Ortho"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-ortho" class="cursor-pointer">ortho</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="specialization-dentist"
                                 inputId="specialization-dentist"
                                 data-testid="doctors-filters-specialization-dentist"
                                 value="Dentist"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-dentist" class="cursor-pointer">Dentist</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="specialization-pediatrics"
                                 inputId="specialization-pediatrics"
                                 data-testid="doctors-filters-specialization-pediatrics"
                                 value="Pediatrics"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-pediatrics" class="cursor-pointer">Pediatrics</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="specialization-neurology"
                                 inputId="specialization-neurology"
                                 data-testid="doctors-filters-specialization-neurology"
                                 value="Neurology"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-neurology" class="cursor-pointer">Neurology</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="specialization-orthopedics"
                                 inputId="specialization-orthopedics"
                                 data-testid="doctors-filters-specialization-orthopedics"
                                 value="Orthopedics"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-orthopedics" class="cursor-pointer">Orthopedics</label>
                </div>
                {{store.query.filter.specialization}}

                <div class="field-radiobutton">
                    <RadioButton name="specialization-cardiology"
                                 inputId="specialization-cardiology"
                                 data-testid="doctors-filters-specialization-cardiology"
                                 value="Cardiology"
                                 v-model="store.query.filter.specialization" />
                    <label for="specialization-cardiology" class="cursor-pointer">Cardiology</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="specialization-dermatology"
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
                    <b>Price Range:</b>
                </template>

                <div class="field-radiobutton">
                    <RadioButton name="price-500-600"
                                 inputId="price-500-600"
                                 data-testid="doctors-filters-price-500-600"
                                 value="500-600"
                                 v-model="store.query.filter.price" />
                    <label for="price-500-600" class="cursor-pointer">500 - 600</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="price-601-700"
                                 inputId="price-601-700"
                                 data-testid="doctors-filters-price-601-700"
                                 value="601-700"
                                 v-model="store.query.filter.price" />
                    <label for="price-601-700" class="cursor-pointer">601 - 700</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="price-701-800"
                                 inputId="price-701-800"
                                 data-testid="doctors-filters-price-701-800"
                                 value="701-800"
                                 v-model="store.query.filter.price" />
                    <label for="price-701-800" class="cursor-pointer">701 - 800</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="price-801-900"
                                 inputId="price-801-900"
                                 data-testid="doctors-filters-price-801-900"
                                 value="801-900"
                                 v-model="store.query.filter.price" />
                    <label for="price-801-900" class="cursor-pointer">801 - 900</label>
                </div>

                <div class="field-radiobutton">
                    <RadioButton name="price-901-1000"
                                 inputId="price-901-1000"
                                 data-testid="doctors-filters-price-901-1000"
                                 value="901-1000"
                                 v-model="store.query.filter.price" />
                    <label for="price-901-1000" class="cursor-pointer">901 - 1000</label>
                </div>

                {{store.query.filter.price}} <!-- For debugging, can be removed later -->
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
                {{ store.query.filter.shift_time }} <!-- For debugging, can be removed later -->
            </VhFieldVertical>








        </Panel>

    </div>
</template>
