<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useAppointmentStore} from '../../stores/store-appointments'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue'

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
        // Initialize fieldMappings based on the number of headers
        fieldMappings.value = new Array(csvHeaders.value.length).fill("");
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
    if (csvData.value) {
        store.importAppointments(csvData.value);
        isModalVisible.value = false; // Close modal
        activeStep.value = 0; // Reset steps
        csvData.value = [];
        fieldMappings.value = [];
        csvHeaders.value = [];
    }
};


// Export appointments
const exportAppointments = () => {
    store.exportAppointments();
};

onMounted(async () => {
    document.title = 'Appointments - Appointments';
    store.item = null;
    /**
     * call onLoad action when List view loads
     */
    await store.onLoad(route);

    /**
     * watch routes to update view, column width
     * and get new item when routes get changed
     */
    await store.watchRoutes(route);

    /**
     * watch states like `query.filter` to
     * call specific actions if a state gets
     * changed
     */
    await store.watchStates();

    /**
     * fetch assets required for the crud
     * operation
     */
    await store.getAssets();

    /**
     * fetch list of records
     */
    await store.getList();

    await store.getListCreateMenu();

});

//--------form_menu
const create_menu = ref();
const toggleCreateMenu = (event) => {
    create_menu.value.toggle(event);
};
//--------/form_menu

</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-'+(store.show_filters?9:store.list_view_width)">
            <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Appointments</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">

                        <Button @click="isModalVisible = true">Upload Appointment CSV</Button>
                        <Button label="Export Appointments" @click="exportAppointments" style="margin-left: 2px;" />
                        <Button
                            v-if="store.hasPermission(store.assets.permission, 'appointments-has-access-of-button')"
                        data-testid="appointments-list-create"
                        class="p-button-sm"
                        @click="store.toForm()"
                    >
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>

                    <Button data-testid="appointments-list-reload"
                            class="p-button-sm"
                            @click="store.getList()">
                        <i class="pi pi-refresh mr-1"></i>
                    </Button>

                    <!--form_menu-->

                    <Button v-if="root.assets && root.assets.module
                                                && root.assets.module.is_dev"
                        type="button"
                        @click="toggleCreateMenu"
                        class="p-button-sm"
                        data-testid="appointments-create-menu"
                        icon="pi pi-angle-down"
                        aria-haspopup="true"/>

                    <Menu ref="create_menu"
                          :model="store.list_create_menu"
                          :popup="true" />

                    <!--/form_menu-->

                    </div>

                </template>

                <Actions/>

                <Table/>

            </Panel>
        </div>

         <Filters/>

        <!-- File Upload Modal -->
        <!-- CSV Upload Modal with steps -->
        <Dialog v-model:visible="isModalVisible" header="Import Appointments" :modal="true" :closable="true" class="custom-file-upload-modal">
            <!-- Stepper UI -->
            <Steps :model="steps" :activeIndex="activeStep" class="custom-steps"></Steps>

            <!-- Step 1: Upload CSV File -->
            <div v-if="activeStep === 0" class="step-content">
                <i class="pi pi-upload icon-large"></i>
                <p>Select a CSV file to upload appointment data from your computer.</p>
                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" class="hidden-file-input" />
                <Button label="Choose File" @click="openFileDialog" class="p-button-rounded p-button-outlined" />
            </div>


            <!-- Step 2: Map Fields -->
            <div v-if="activeStep === 1" class="step-content">
                <i class="pi pi-sitemap icon-large"></i>
                <p>Map the CSV headers to the corresponding fields.</p>
                <div v-for="(header, index) in csvHeaders" :key="index" class="mapping-field">
                    <label>{{ header }}</label>
                    <Dropdown v-model="fieldMappings[index]" :options="csvHeaders" option-label="header" placeholder="-- Select Field --" class="field-dropdown" />
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
                        <th v-for="(header, index) in csvHeaders" :key="index">{{ header }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(row, rowIndex) in csvData" :key="rowIndex">
                        <td v-for="(header, index) in csvHeaders" :key="index">{{ row[header] }}</td>
                    </tr>
                    </tbody>
                </table>
                <Button label="Confirm & Upload" @click="activeStep = 3" class="p-button-rounded p-button-outlined" />
            </div>

            <!-- Step 4: Confirm & Upload -->
            <div v-if="activeStep === 3" class="step-content">
                <i class="pi pi-check icon-large"></i>
                <p>Confirm the upload of appointment data.</p>
                <Button label="Upload" class="p-button-success p-button-rounded" @click="importAppointments" />
                <Button label="Back" class="p-button-secondary p-button-rounded" @click="activeStep = 2" />
            </div>
        </Dialog>


        <RouterView/>

    </div>


</template>

<style>
.custom-file-upload-modal {
    max-width: 400px;
    width: 100%;
    text-align: center;
}

.file-upload-modal-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
}

.icon-large {
    font-size: 4rem;
    color: #4CAF50; /* Light green for a modern look */
}


.hidden-file-input {
    display: none;
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

.field-dropdown {
    width: 40%; /* Set width of dropdown to be less */
    border-radius: 0; /* Remove rounded corners */
    border: 1px solid #ccc; /* Add a subtle border */
    padding: 0.5rem; /* Add some padding for a better look */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Optional: add a slight shadow for depth */
    transition: border-color 0.3s ease; /* Smooth border color transition */
}

.field-dropdown:focus {
    border-color: #4CAF50; /* Change border color on focus */
    outline: none; /* Remove default outline */
}

</style>

