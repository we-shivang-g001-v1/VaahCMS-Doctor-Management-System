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
                    'rgba(249, 115, 22, 0.6)', // More vibrant orange
                    'rgba(6, 182, 212, 0.6)', // Brighter teal
                    'rgba(139, 92, 246, 0.6)', // More vivid purple
                    'rgba(34, 197, 94, 0.6)', // Livelier green
                    'rgba(234, 88, 12, 0.6)', // Brighter red
                    'rgba(255, 193, 7, 0.6)', // Bright yellow
                    'rgba(66, 133, 244, 0.6)', // Vivid blue
                    'rgba(251, 191, 36, 0.6)', // Bright gold
                    'rgba(220, 38, 38, 0.6)', // Bright red
                    'rgba(244, 114, 182, 0.6)' // Pink
                ],
                borderColor: [
                    'rgb(249, 115, 22)',
                    'rgb(6, 182, 212)',
                    'rgb(139, 92, 246)',
                    'rgb(34, 197, 94)',
                    'rgb(234, 88, 12)',
                    'rgb(255, 193, 7)',
                    'rgb(66, 133, 244)',
                    'rgb(251, 191, 36)',
                    'rgb(220, 38, 38)',
                    'rgb(244, 114, 182)'
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
                <Card :class="'card card-total'">
                    <template #title>Total Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.total_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Total appointments scheduled.</p>

                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card card-booked'">
                    <template #title>Booked Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Appointments successfully Booked.</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card card-cancelled'">
                    <template #title>Cancelled Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.cancelled_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Appointments that were cancelled.</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card card-doctors'">
                    <template #title>Booked Doctors</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_doctor_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">doctors with booked appointments.</p>
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
    border-radius: 8px;
    padding: 16px;
    color: white; /* Adjust text color for better contrast */
}

.card:hover {
    transform: scale(1.05);
}

/* Card Background Colors */
.card-total {
    background-color: rgba(249, 115, 22, 0.8); /* Orange */
}

.card-booked {
    background-color: rgba(6, 182, 212, 0.8); /* Teal */
}

.card-cancelled {
    background-color: rgba(139, 92, 246, 0.8); /* Purple */
}

.card-doctors {
    background-color: rgba(34, 197, 94, 0.8); /* Green */
}

/* Other existing styles remain unchanged */

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
