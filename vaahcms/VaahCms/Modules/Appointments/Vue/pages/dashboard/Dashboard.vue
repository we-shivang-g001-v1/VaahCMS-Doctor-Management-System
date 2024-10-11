<script setup>
import { useAppointmentStore } from "../../stores/store-appointments";
import { ref, onMounted } from "vue";

document.title = 'Appointments';
const store = useAppointmentStore();

// States for loading and error handling
const loading = ref(true); // Track loading status
const error = ref(null); // Track error messages

onMounted(async () => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
    try {
        // Call the getAppointmentList function from the store when the component is mounted
        await store.getAppointmentList();
        console.log(store.item.counts); // Log the counts after successful fetch
    } catch (err) {
        error.value = 'Failed to load appointment data. Please try again later.'; // Set error message
        console.error('Error loading appointment data:', err); // Log the error for debugging
    } finally {
        loading.value = false; // Set loading to false regardless of success or failure
    }
});

const chartData = ref();
const chartOptions = ref();


const setChartData = () => {
    return {
        labels: ['Q1', 'Q2', 'Q3', 'Q4'],
        datasets: [
            {
                label: 'Sales',
                data: [540, 325, 702, 620],
                backgroundColor: ['rgba(249, 115, 22, 0.2)', 'rgba(6, 182, 212, 0.2)', 'rgb(107, 114, 128, 0.2)', 'rgba(139, 92, 246 0.2)'],
                borderColor: ['rgb(249, 115, 22)', 'rgb(6, 182, 212)', 'rgb(107, 114, 128)', 'rgb(139, 92, 246)'],
                borderWidth: 1
            }
        ]
    };
};
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
}
</script>

<template>
    <div style="margin-top: 8px;">
        <h1 class="text-4xl font-bold">Dashboard</h1>

        <!-- Loading State -->
        <div v-if="loading" class="loading-message">
            Loading appointments...
        </div>

        <!-- Error State -->
        <div v-if="error" class="error-message text-red-600">
            {{ error }}
        </div>

        <!-- Render content only if data is loaded and no errors -->
        <div v-if="!loading && !error" class="grid mt-4">
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Total Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.total_count }}</h2>
                        <p class="m-0 text-gray-700">Total number of appointments scheduled.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Booked Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_count }}</h2>
                        <p class="m-0 text-gray-700">Appointments successfully completed.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Cancelled Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.cancelled_count }}</h2>
                        <p class="m-0 text-gray-700">Appointments awaiting confirmation.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Cancelled Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.cancelled_count }}</h2>
                        <p class="m-0 text-gray-700">Appointments that were cancelled.</p>
                        <Button label="View Details" class="p-button-sm mt-2" />
                    </template>
                </Card>
            </div>


                <div class="card col-10 md:col-10">
                    <Chart type="bar" :data="chartData" :options="chartOptions" />
                </div>

        </div>


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
.loading-message {
    font-size: 1.5rem;
    text-align: center;
    margin-top: 20px;
}
.error-message {
    font-size: 1.2rem;
    text-align: center;
    margin-top: 20px;
    color: red; /* Add any desired styling for the error message */
}
</style>
