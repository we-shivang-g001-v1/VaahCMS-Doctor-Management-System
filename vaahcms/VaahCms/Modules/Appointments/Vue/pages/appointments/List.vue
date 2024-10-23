<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute } from 'vue-router';
import { useAppointmentStore } from '../../stores/store-appointments';
import { useRootStore } from '../../stores/root';
import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue';
const store = useAppointmentStore();
const root = useRootStore();
const route = useRoute();
import { useConfirm } from "primevue/useconfirm";
const confirm = useConfirm();

// Modal and file upload handling
const isModalVisible = ref(false);
const fileInput = ref(null);
const csvData = ref([]);
const csvHeaders = ref([]);
const fieldMappings = ref([]);
const activeStep = ref(0); // Track current step in CSV upload process
const steps = ref([
    { label: "Upload CSV" },
    { label: "Map Fields" },
    { label: "Preview Data" },
    { label: "Confirm & Upload" },
]);

// Open file dialog
const openFileDialog = () => {
    fileInput.value.click();
};

// Handle file upload and extract CSV data
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            csvData.value = csvToJson(e.target.result);
            console.log("CSV Data:", csvData.value); // Log CSV data
            extractHeaders();
            activeStep.value = 1; // Move to step 2 after upload
        };
        reader.readAsText(file);
    }
};

// CSV to JSON conversion
const csvToJson = (csv) => {
    const lines = csv.split("\n");
    const result = [];
    const headers = lines[0].split(",");
    for (let i = 1; i < lines.length; i++) {
        const obj = {};
        const currentLine = lines[i].split(",");
        for (let j = 0; j < headers.length; j++) {
            obj[headers[j].trim()] = currentLine[j] ? currentLine[j].trim() : "";
        }
        result.push(obj);
    }
    return result;
};

// Extract CSV headers for mapping
const extractHeaders = () => {
    if (csvData.value && csvData.value.length > 0) {
        csvHeaders.value = Object.keys(csvData.value[0]);
        console.log("Extracted CSV Headers:", csvHeaders.value); // Log extracted headers
        fieldMappings.value = new Array(store.assets.fields.length).fill(""); // Link to store.fields
    }
};

// Proceed to preview data after field mapping
const mapFieldsAndPreview = () => {
    if (fieldMappings.value.length > 0) {
        console.log("Field Mappings:", fieldMappings.value); // Log field mappings
        activeStep.value = 2; // Move to step 3 for preview
    }
};


// Import mapped appointments
const importAppointments = () => {
    const mappedData = csvData.value.map(row => {
        const mappedRow = {};
        fieldMappings.value.forEach((csvHeader, index) => {
            if (csvHeader) {
                const field = store.assets.fields[index];
                mappedRow[field] = row[csvHeader];
            }
        });
        return mappedRow;
    });

    console.log("Mapped Appointment Data for Upload:", mappedData);
    store.importAppointments(mappedData);
    isModalVisible.value = false; // Close modal
    activeStep.value = 0; // Reset steps
    csvData.value = [];
    fieldMappings.value = [];
    csvHeaders.value = [];
};

// Export appointments
const exportAppointments = () => {
    store.exportAppointments();
    console.log(store.exportAppointments())
};

onMounted(async () => {
    document.title = 'Appointments - Appointments';
    store.item = null;
    await store.onLoad(route);
    await store.watchRoutes(route);
    await store.watchStates();
    await store.getAssets();
    await store.getList();
    await store.getListCreateMenu();
});

const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
</script>

<template>
    <div class="grid" v-if="store.assets">
        <div v-if="$isMobile()" :class="'col-' + (store.show_filters ? 6 : store.list_view_width)">
            <Panel class="is-small mobile-panel">
                <template #header>
                    <div class="flex flex-row justify-between align-items-center">
                        <div>
                            <b class="mr-1">Appointments mobile</b>
                            <Badge v-if="store.list && store.list.total > 0" :value="store.list.total" />
                        </div>
<!--                        <Button icon="pi pi-refresh" class="p-button-sm" @click="store.getList()" />-->
                    </div>
                </template>

                <template #icons>
<!--                    <div class="p-inputgroup mobile-buttons">-->
<!--                        <Button @click="isModalVisible = true" class="p-button-sm full-width mb-1">Upload CSV</Button>-->
<!--                        <Button label="Export" @click="exportAppointments" class="p-button-sm full-width mb-1" />-->
<!--                        <Button v-if="store.assets.permission[1] !== 'appointments-has-access-of-doctor-section'"-->
<!--                                class="p-button-sm full-width mb-1"-->
<!--                                @click="store.toForm()">-->
<!--                            <i class="pi pi-plus mr-1"></i> Create-->
<!--                        </Button>-->
<!--                        <Button v-if="root.assets && root.assets.module && root.assets.module.is_dev"-->
<!--                                type="button"-->
<!--                                @click="toggleCreateMenu"-->
<!--                                class="p-button-sm full-width mb-1"-->
<!--                                icon="pi pi-angle-down" />-->
<!--                    </div>-->
                </template>

                <Actions />
                <Table />
            </Panel>
        </div>


        <div v-if="!$isMobile()" :class="'col-'+(store.show_filters?9:store.list_view_width)">
            <Panel class="is-small">
                <template class="p-1" #header>
                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Appointments</b>
<!--                            {{store.assets.fields}}-->
                            <Badge v-if="store.list && store.list.total > 0" :value="store.list.total"></Badge>
                        </div>
                    </div>
                </template>

                <template #icons>
                    <div class="p-inputgroup">
                        <Button @click="isModalVisible = true">Upload Appointment CSV</Button>
                        <Button label="Export Appointments" @click="exportAppointments" style="margin-left: 2px;" />
                        <Button
                            v-if="store.assets.permission[1] !== 'appointments-has-access-of-doctor-section'"
                            data-testid="appointments-list-create"
                            class="p-button-sm"
                            @click="store.toForm()"
                        >
                            <i class="pi pi-plus mr-1"></i>
                            Create
                        </Button>

                        <Button data-testid="appointments-list-reload" class="p-button-sm" @click="store.getList()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>

                        <Button v-if="root.assets && root.assets.module && root.assets.module.is_dev" type="button" @click="toggleCreateMenu" class="p-button-sm" data-testid="appointments-create-menu" icon="pi pi-angle-down" aria-haspopup="true"/>

                        <Menu ref="create_menu" :model="store.list_create_menu" :popup="true" />
                    </div>
                </template>

                <Actions />
                <Table />
            </Panel>
        </div>

        <Filters />

        <RouterView />

        <!-- CSV Upload Modal with steps -->
        <Dialog v-model:visible="isModalVisible" header="Import Appointments" :modal="true" :closable="true" class="custom-file-upload-modal">
            <Steps :model="steps" :activeIndex="activeStep" class="custom-steps"></Steps>

            <!-- Step 1: Upload CSV File -->
            <div v-if="activeStep === 0" class="step-content">
                <i class="pi pi-upload icon-large"></i>
                <p>Select a CSV file to upload appointment data from your computer.</p>
                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" class="hidden-file-input" />
                <Button label="Choose File" @click="openFileDialog" class="p-button-rounded p-button-outlined" />

                <Button label="Download Sample File" icon="pi pi-download" @click="downloadSampleFile" class="p-button-rounded p-button-outlined" />

            </div>

            <!-- Step 2: Map Fields -->
            <div v-if="activeStep === 1" class="step-content">
                <i class="pi pi-sitemap icon-large"></i>
                <p>Map the CSV headers to the corresponding fields.</p>
                <div class="mapping-fields-container">
                    <div v-for="(field, index) in store.assets.fields" :key="index" class="mapping-field">
                        <label>{{ field }}</label>
                        <select v-model="fieldMappings[index]" class="field-dropdown">
                            <option disabled value="">-- Select Field --</option>
                            <option v-for="(csvHeader, csvIndex) in csvHeaders" :key="csvIndex" :value="csvHeader">
                                {{ csvHeader }}
                            </option>
                        </select>
                    </div>
                </div>
                <Button label="Preview Data" @click="mapFieldsAndPreview" class="p-button-rounded p-button-outlined" />
            </div>

            <!-- Step 3: Preview Data -->
            <div v-if="activeStep === 2" class="step-content">
                <i class="pi pi-eye icon-large"></i>
                <p>Preview the mapped data before importing.</p>
                <table class="preview-table">
                    <thead>
                    <tr>
                        <th v-for="(field, index) in store.assets.fields" :key="index">{{ field }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(row, rowIndex) in csvData" :key="rowIndex">
                        <td v-for="(mapping, index) in fieldMappings" :key="index">{{ row[mapping] }}</td>
                    </tr>
                    </tbody>
                </table>
                <Button label="Confirm & Upload" @click="activeStep = 3" class="p-button-rounded p-button-outlined" />
            </div>

            <!-- Step 4: Confirm & Upload -->
            <div v-if="activeStep === 3" class="step-content">
                <i class="pi pi-check-circle icon-large"></i>
                <p>Confirm the appointment data upload.</p>
                <Button label="Upload Appointments" @click="importAppointments" class="p-button-rounded p-button-outlined" />
            </div>
        </Dialog>
    </div>
</template>



<style>

.mapping-fields-container {
    display: flex;
    flex-direction: column;
    gap: 1rem; /* Space between mapping fields */
    width: 100%; /* Ensures full width */
}

.mapping-field {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Space between label and dropdown */
    padding: 0.5rem; /* Adds padding for better spacing */
    border: 1px solid #ccc; /* Adds a border around each mapping field */
    border-radius: 4px; /* Rounded corners */
    background-color: #f9f9f9; /* Light background */
}

.mapping-field label {
    flex: 1; /* Label takes up available space */
    margin-right: 1rem; /* Space between label and dropdown */
    font-weight: bold; /* Make label bold */
}

.field-dropdown {
    flex: 2; /* Dropdown takes up more space */
    padding: 0.5rem; /* Padding inside the dropdown */
    border: 1px solid #ccc; /* Border around dropdown */
    border-radius: 4px; /* Rounded corners */
    background-color: #fff; /* White background for dropdown */
    transition: border-color 0.3s; /* Smooth transition for focus */
}

.field-dropdown:focus {
    border-color: #4CAF50; /* Change border color on focus */
    outline: none; /* Remove default outline */
}

.field-dropdown option {
    padding: 0.5rem; /* Padding inside dropdown options */
}

/* Optional: Style for the button */
.p-button-rounded {
    border-radius: 25px;
    margin-top: 1rem; /* Space above the button */
}

.custom-file-upload-modal {
    max-width: 1500px;
    width: 100%;
    text-align: center;
}
.step-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}
.icon-large {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #4CAF50;
}
.mapping-field {
    margin-bottom: 1rem;
    width: 100%;
}
.custom-file-upload-modal {
    max-width: 1500px; /* Increased width for a larger dialog */
    width: 100%;
    text-align: center;
    padding: 1.5rem; /* Added padding for a more spacious look */
}

.custom-steps {
    margin-bottom: 2rem; /* Added margin for stepper */
    justify-content: center; /* Centered stepper */
}

.step-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.icon-large {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #4CAF50;
}

.mapping-field {
    margin-bottom: 1.5rem; /* Increased spacing between mapping fields */
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.field-dropdown {
    width: 60%; /* Set width of dropdowns */
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.preview-table th, .preview-table td {
    padding: 0.75rem;
    border: 1px solid #ddd;
    text-align: left;
}

.p-button-rounded {
    border-radius: 25px;
    margin: 1rem 0;
}

.p-button-outlined {
    border: 2px solid #4CAF50;
    color: #4CAF50;
    background-color: white;
}

/* Mobile panel styling */
.mobile-panel {
    margin: 1rem;
    padding: 1rem;
    border-radius: 8px; /* Rounded corners for modern look */
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
}

/* Aligns header elements */
.mobile-panel .flex {
    justify-content: space-between; /* Space out header content */
    align-items: center; /* Vertical alignment */
}

/* Full-width buttons */
.mobile-buttons .full-width {
    width: 100%; /* Make buttons fill the container */
}

/* Spacing between buttons */
.mobile-buttons .mb-1 {
    margin-bottom: 0.5rem; /* Space between buttons */
}

/* Badge styling */
.p-badge {
    margin-left: 0.5rem; /* Space between badge and text */
}


</style>
