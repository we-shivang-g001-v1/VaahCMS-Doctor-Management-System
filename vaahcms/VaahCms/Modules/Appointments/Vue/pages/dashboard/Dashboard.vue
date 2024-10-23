<script setup>
import { useAppointmentStore } from "../../stores/store-appointments";
import { ref, onMounted } from "vue";
import Chart from 'primevue/chart'; // Import Chart component

document.title = 'Appointments';
const store = useAppointmentStore();

// States for loading and error handling
const loading = ref(true);
const error = ref(null);
const chartData = ref({});
const chartOptions = ref({});
const pieChartData = ref({}); // New state for pie chart data
const pieChartOptions = ref({}); // New state for pie chart options

onMounted(async () => {
    chartOptions.value = setChartOptions();
    pieChartOptions.value = setPieChartOptions(); // Set pie chart options

    try {
        // Fetch appointment data
        await store.getAppointmentList();

        // Set the chart data based on the fetched appointment counts
        chartData.value = setChartData(store.item.counts);
        pieChartData.value = setPieChartData(store.item.counts); // Set pie chart data

    } catch (err) {
        error.value = 'Failed to load appointment data. Please try again later.';
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
                ],
                backgroundColor: [
                    'rgba(169, 169, 169, 0.6)', // Dark gray
                    'rgba(211, 211, 211, 0.6)', // Light gray
                    'rgba(128, 128, 128, 0.6)', // Gray
                    'rgba(192, 192, 192, 0.6)', // Silver
                    'rgba(105, 105, 105, 0.6)', // Dim gray
                ],
                borderColor: [
                    'rgb(169, 169, 169)', // Dark gray
                    'rgb(211, 211, 211)', // Light gray
                    'rgb(128, 128, 128)', // Gray
                    'rgb(192, 192, 192)', // Silver
                    'rgb(105, 105, 105)', // Dim gray
                ],
                borderWidth: 1
            }
        ]
    };
};

const setPieChartData = (counts) => {
    return {
        labels: ['Booked', 'Cancelled'],
        datasets: [
            {
                data: [counts.booked_count, counts.cancelled_count],
                backgroundColor: [
                    'rgba(192, 192, 192, 0.6)', // Silver for booked
                    'rgba(128, 128, 128, 0.6)'  // Gray for cancelled
                ]
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

const setPieChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');

    return {
        plugins: {
            legend: {
                labels: {
                    color: textColor
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
                <Card :class="'card'">
                    <template #title>Total Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.total_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Total appointments scheduled</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card'">
                    <template #title>Booked Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Appointments successfully booked</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card'">
                    <template #title>Cancelled Appointments</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.cancelled_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Appointments that were cancelled</p>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card'">
                    <template #title>Booked Doctors</template>
                    <template #content>
                        <h2 class="text-3xl font-semibold">{{ store.item.counts.booked_doctor_count }}</h2><br>
                        <p class="m-0 font-bold text-lg text-gray-700">Doctors with booked appointments</p>
                    </template>
                </Card>
            </div>

            <div class="chart-container">
                <!-- Bar Chart -->
                <div class="chart-wrapper bar-chart">
                    <Chart type="bar" :data="chartData" :options="chartOptions" class="chart" />
                </div>
                <!-- Pie Chart -->
                <div class="chart-wrapper pie-chart">
                    <Chart type="pie" :data="pieChartData" :options="pieChartOptions" class="chart" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
}
.col-12 {
    flex: 0 0 100%;
}
.md\:col-3 {
    flex: 0 0 24%;
}
.card {
    background-color: #f7fafc; /* Light gray background */
    color: black; /* Set text color to black */
    transition: transform 0.3s;
    border-radius: 8px;
    padding: 16px;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    border: 1px solid #e2e8f0; /* Light border */
}

.card:hover {
    transform: scale(1.05);
}

.loading-message,
.error-message {
    font-size: 1.5rem;
    text-align: center;
    margin-top: 20px;
}

/* Chart container styles */
.chart-container {
    display: flex; /* Align charts side by side */
    justify-content: space-around; /* Adjust to space-around for more space between charts */
    width: 100%; /* Full width of the container */
}

.chart-wrapper.bar-chart {
    width: 60%; /* Bar chart takes 60% of the width */
    margin: 20px; /* Maintain the same margin */
}

.chart-wrapper.pie-chart {
    width: 40%; /* Pie chart takes 40% of the width */
    margin: 20px; /* Maintain the same margin */
}

.chart {
    height: 400px; /* Set the same height for both charts */
    width: 100%; /* Full width of the container */
}

@media (max-width: 768px) {
    .md\:col-3 {
        flex: 0 0 48%;
    }

    .chart-wrapper {
        width: 100%; /* Full width on smaller screens */
    }
}

@media (max-width: 576px) {
    .md\:col-3 {
        flex: 0 0 100%; /* Full width for small screens */
    }
}
</style>
