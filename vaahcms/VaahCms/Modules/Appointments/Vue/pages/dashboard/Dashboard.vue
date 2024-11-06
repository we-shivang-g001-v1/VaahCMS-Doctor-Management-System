<script setup>
import { useAppointmentStore } from "../../stores/store-appointments";
import { ref, onMounted } from "vue";
import Chart from 'primevue/chart'; // Import Chart component
import CustomChart from "./components/CustomChart.vue";

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
        pieChartData.value = setPieChartData(store.item.counts);

        chart_series.value = [
            {
                name: 'Appointments Count',
                data: [
                    store.item?.counts?.total_count, // Correctly referencing counts from the store
                    store.item?.counts?.booked_count,
                    store.item?.counts?.cancelled_count,
                    store.item?.counts?.booked_doctor_count,
                    store.item?.counts?.booked_patient_count
                ]
            }
        ];
        // Set pie chart data

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
        labels: ['Doctors', 'Patients'],
        datasets: [
            {
                data: [counts.total_doctor_count, counts.total_patient_count],
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
const chart_series = ref([
    {
        name: 'Appointments Count',
        data: [
            store.item?.counts?.total_count, // Correctly referencing counts from the store
            store.item?.counts?.booked_count,
            store.item?.counts?.cancelled_count,
            store.item?.counts?.booked_doctor_count,
            store.item?.counts?.booked_patient_count
        ]
    }
]);
const chart_options = ref({
    chart: {
        stacked: false,
    },
    plotOptions: {
        bar: {},
    },
    xaxis: {
        categories: ['Total Appointments','Booked Appointments','Cancelled Appointments','Booked Doctors','Booked Patients'],
    },
    yaxis: {
        title: {
            text: 'Count',
        },
    },
    title: {
        text: 'Appointments Count',
        align: 'center',
    },
});
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
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-user" style="margin-right: 8px; font-size: 2rem;"></i>
                            <span class="title-text">Total Appointments</span>
                        </div>
                    </template>
                    <template #content>
                        <h2 class="count-text">{{ store.item.counts.total_count }}</h2><br>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card'">
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-calendar-plus" style="margin-right: 8px; font-size: 2rem;"></i>
                            <span class="title-text">Booked Appointments</span>
                        </div>
                    </template>
                    <template #content>
                        <h2 class="count-text">{{ store.item.counts.booked_count }}</h2><br>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card'">
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-calendar-times" style="margin-right: 8px; margin-left: 1px; font-size: 2rem;"></i>
                            <span class="title-text">Cancelled Appointments</span>
                        </div>
                    </template>
                    <template #content>
                        <h2 class="count-text">{{ store.item.counts.cancelled_count }}</h2><br>
                    </template>
                </Card>
            </div>
            <div class="col-12 md:col-3">
                <Card :class="'card'">
                    <template #title>
                        <div class="flex items-center">
                            <i class="pi pi-users" style="margin-right: 8px; font-size: 2rem;"></i>
                            <span class="title-text">Booked Doctors</span>
                        </div>
                    </template>
                    <template #content>
                        <h2 class="count-text">{{ store.item.counts.booked_doctor_count }}</h2><br>
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

            <div class="chart-container">

                <div class="chart-wrapper bar-chart">
                    <CustomChart
                        type="bar"
                        title='Customer Count Bar Chart'
                        height="400"
                        width="800"
                        titleAlign="center"
                        :chartSeries="chart_series"
                        :chartOptions="chart_options"
                    />
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
.loading-message,
.error-message {
    font-size: 1.5rem;
    text-align: center;
    margin-top: 20px;
}

.chart-container {
    display: flex;
    justify-content: space-between; /* Space between charts */
    width: 100%;
    max-width: 100vw; /* Prevent scaling beyond 100% of viewport width */
    max-height: 80vw; /* Prevent scaling beyond 100% of viewport width */
    overflow: hidden; /* Hide overflow */
}

.chart-wrapper.bar-chart {
    flex: 0 0 66%; /* Take up 66% of the container */
    max-width: 66%; /* Prevent scaling beyond 66% */
    margin: 20px;
}

.chart-wrapper.pie-chart {
    flex: 0 0 34%; /* Take up 34% of the container */
    max-width: 34%; /* Prevent scaling beyond 34% */
    margin: 20px;
}

.card {
    background-color: #f7fafc;
    color: black;
    transition: transform 0.3s;
    border-radius: 8px;
    padding: 16px;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-height: 75%;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    max-width: 100%; /* Prevent cards from scaling beyond 100% of their parent */
    box-sizing: border-box; /* Include padding and border in width calculations */
}

.chart {
    height: 400px;
    width: 100%;
}

.title-text {
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex-grow: 1;
    font-size: 20px;
}

.count-text {
    font-size: 2rem;
}

.flex {
    display: flex;
    align-items: center;
}

/* Adjust text sizes for smaller screens */
@media (max-width: 768px) {
    .md\:col-3 {
        flex: 0 0 48%;
    }

    .title-text {
        font-size: 1.25rem; /* Smaller font for titles */
    }
    .count-text {
        font-size: 1.5rem; /* Smaller font for counts */
    }
}

@media (max-width: 576px) {
    .md\:col-3 {
        flex: 0 0 100%;
    }
    .title-text {
        font-size: 1.1rem; /* Further reduce font size for titles */
    }
    .count-text {
        font-size: 1.25rem; /* Further reduce font size for counts */
    }
}
</style>

