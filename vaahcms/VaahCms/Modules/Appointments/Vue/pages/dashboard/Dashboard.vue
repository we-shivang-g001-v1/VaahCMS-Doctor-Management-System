<script setup>
import { useAppointmentStore } from "../../stores/store-appointments";
import { ref, onMounted, watch } from "vue";

document.title = 'Appointments';
const store = useAppointmentStore();

// States for loading and error handling
const loading = ref(true);
const error = ref(null);
const chartData = ref({});
const chartOptions = ref({});

onMounted(async () => {
    chartOptions.value = setChartOptions();

    try {
        // Fetch appointment data
        await store.getAppointmentList();

        // Set the chart data based on the fetched appointment counts
        chartData.value = setChartData(store.item.counts);

    } catch (err) {
        error.value = 'Failed to load appointment data. Please try again later.';
        console.error('Error loading appointment data:', err);
    } finally {
        loading.value = false;
    }
});

const setChartData = (counts) => {
    return {
        labels: [
            'Total Appointments',
            'Booked Appointments',
            'Cancelled Appointments',
            'Booked Doctors',
            'Booked Patients'
        ],
        datasets: [
            {
                label: 'Appointment Stats',
                data: [
                    counts.total_count,
                    counts.booked_count,
                    counts.cancelled_count,
                    counts.booked_doctor_count,
                    counts.booked_patient_count
                ], // Set the counts here
                backgroundColor: [
                    'rgba(249, 115, 22, 0.2)',
                    'rgba(6, 182, 212, 0.2)',
                    'rgba(139, 92, 246, 0.2)',
                    'rgba(34, 197, 94, 0.2)',
                    'rgba(234, 88, 12, 0.2)'
                ],
                borderColor: [
                    'rgb(249, 115, 22)',
                    'rgb(6, 182, 212)',
                    'rgb(139, 92, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(234, 88, 12)'
                ],
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
};
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
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Booked Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_count }}</h2>
                        <p class="m-0 text-gray-700">Appointments successfully completed.</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Cancelled Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.cancelled_count }}</h2>
                        <p class="m-0 text-gray-700">Appointments that were cancelled.</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card>
                    <template #title>Booked Doctors</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_doctor_count }}</h2>
                        <p class="m-0 text-gray-700">Unique doctors with booked appointments.</p>
                    </template>
                </Card>
            </div>

            <!-- Chart -->
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
