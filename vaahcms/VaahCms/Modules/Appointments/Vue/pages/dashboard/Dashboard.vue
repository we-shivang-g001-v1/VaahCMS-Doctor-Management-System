<script setup>
import {useAppointmentStore} from "../../stores/store-appointments";
import {vaah} from "../../vaahvue/pinia/vaah";
import {onMounted} from "vue";
document.title = 'Appointments';
const store = useAppointmentStore();

onMounted(async () => {
    // Call the getAppointmentList function from the store when component is mounted
    await store.getAppointmentList();
console.log(store.item.counts)
});

</script>

<template>
    <div style="margin-top: 8px;">
        <h1 class="text-4xl font-bold">Dashboard</h1>
        <h2 class="text-2xl mt-5 font-bold">Appointments Overview</h2>
        <div class="grid mt-4">
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Total Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{store.item.counts.total_count}}</h2>
                        <p class="m-0 text-gray-700">Total number of appointments scheduled.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Booked Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{store.item.counts.booked_count}}</h2>
                        <p class="m-0 text-gray-700">Appointments successfully completed.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Cancel Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{store.item.counts.cancelled_count}}</h2>
                        <p class="m-0 text-gray-700">Appointments awaiting confirmation.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Cancelled Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{store.item.counts.cancelled_count}}</h2>
                        <p class="m-0 text-gray-700">Appointments that were cancelled.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
        </div>


        <!-- <Chart type="bar" :data="appointmentsData" class="m-2" /> -->
    </div>
</template>

<style scoped>
.grid {
    display: flex;
    flex-wrap: wrap;
}
.col-12 {
    flex: 0 0 100%;
}
.md\:col-3 {
    flex: 0 0 25%;
}
.card {
    transition: transform 0.3s;
}
.card:hover {
    transform: scale(1.05);
}
</style>
