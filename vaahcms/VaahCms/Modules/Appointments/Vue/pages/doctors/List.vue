<script setup>
import { onMounted, ref ,watch } from "vue";
import { useRoute } from "vue-router";
import { useDoctorStore } from '../../stores/store-doctors';
import { useRootStore } from '../../stores/root';
import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import CustomFilter from "./components/CustomFilter.vue";
import Filters from "./components/Filters.vue";
import { useConfirm } from "primevue/useconfirm";
const store = useDoctorStore();
const root = useRootStore();
const route = useRoute();
const confirm = useConfirm();

// Modal and file upload handling
const isModalVisible = ref(false);
const fileInput = ref(null);
const csvData = ref([]);
const csvHeaders = ref([]);
const fieldMappings = ref([]);
const active_step = ref(0); // Track current step in CSV upload process
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
            extractHeaders();
            active_step.value = 1; // Move to step 2 after upload
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
        fieldMappings.value = new Array(store.assets.fields.length).fill("");
    }
};

// Proceed to preview data after field mapping
const mapFieldsAndPreview = () => {
    if (fieldMappings.value.length > 0) {
        active_step.value = 2; // Move to step 3 for preview
    }
};

watch(isModalVisible, (newValue) => {
    if (!newValue) {
        // Reset all data and steps when modal is closed
        active_step.value = 0;
        csvData.value = [];
        fieldMappings.value = [];
        csvHeaders.value = [];
    }
});
// Import mapped doctors
const importDoctors = () => {
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

    store.importDoctors(mappedData);
    isModalVisible.value = false; // Close modal, triggering the watcher

};



const exportDoctors = () => {
    store.exportDoctors();
};

const downloadDoctorSampleFile = () => {
    store.downloadDoctorSampleFile();
};

onMounted(async () => {
    document.title = 'Doctors CSV';
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
        <div :class="'col-' + ((store.show_filters || store.show_custom_filters) ? 9 : store.list_view_width)">
            <Panel class="is-small">
                <template class="p-1" #header>
                    <div class="flex flex-row">
                        <div>
                            <b class="mr-1">Doctors</b>
                            <Badge v-if="store.list && store.list.total > 0" :value="store.list.total"></Badge>
                        </div>
                    </div>
                </template>

                <template #icons>
                    <div class="p-inputgroup">
                        <Button @click="isModalVisible = true">Upload Doctors CSV</Button>
                        <Button label="Export Doctors" @click="exportDoctors" style="margin-left: 2px;" />
                        <Button data-testid="doctors-list-create" class="p-button-sm" @click="store.toForm()">
                            <i class="pi pi-plus mr-1"></i> Create
                        </Button>
                        <Button data-testid="doctors-list-reload" class="p-button-sm" @click="store.getList()">
                            <i class="pi pi-refresh mr-1"></i>
                        </Button>
                        <Button v-if="root.assets && root.assets.module && root.assets.module.is_dev"
                                type="button"
                                @click="toggleCreateMenu"
                                class="p-button-sm"
                                data-testid="doctors-create-menu"
                                icon="pi pi-angle-down"
                                aria-haspopup="true" />
                        <Menu ref="create_menu" :model="store.list_create_menu" :popup="true" />
                    </div>
                </template>

                <Actions />
                <Table />
            </Panel>
        </div>

        <Filters />
        <CustomFilter />

        <!-- CSV Upload Modal with steps -->
        <Dialog v-model:visible="isModalVisible" header="Import Doctors" :modal="true" :closable="true" class="custom-file-upload-modal">
            <Steps :model="steps" v-model:activeStep="active_step" class="custom-steps"></Steps>

            <!-- Step 1: Upload CSV File -->
            <div v-if="active_step === 0" class="step-content">
                <i class="pi pi-upload icon-large"></i>
                <p>Select a CSV file to upload Doctors data from your computer.</p>
                <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" class="hidden-file-input" />
                <Button label="Choose File" @click="openFileDialog" class="p-button-rounded p-button-outlined" />

                <Button label="Download Sample File" icon="pi pi-download" @click="downloadDoctorSampleFile" class="p-button-rounded p-button-outlined" />

            </div>

            <!-- Step 2: Map Fields -->
            <div v-if="active_step === 1" class="step-content">
                <i class="pi pi-sitemap icon-large"></i>
                <p>Map the CSV headers to the corresponding fields.</p>
                <div class="mapping-fields-container">
                    <div v-for="(field, index) in store.assets.fields" :key="index" class="mapping-field">
                        <label>
                            {{ field }}
                            <span v-if="index <= 6" class="required">*</span>
                        </label>
                        <select v-model="fieldMappings[index]" class="field-dropdown" :required="index <= 4">
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
            <div v-if="active_step === 2" class="step-content">
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
                <Button label="Confirm & Upload" @click="active_step = 3" class="p-button-rounded p-button-outlined" />
            </div>

            <!-- Step 4: Confirm & Upload -->
            <div v-if="active_step === 3" class="step-content">
                <i class="pi pi-check-circle icon-large"></i>
                <p>Confirm the Doctors data upload.</p>
                <Button label="Upload Doctors" @click="importDoctors" class="p-button-rounded p-button-outlined" />
            </div>
        </Dialog>


        <RouterView />
    </div>
</template>

<style>
.hidden-file-input {
    display: none;
}

.mapping-fields-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
}

.mapping-field {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;
    margin-bottom: 1.5rem;
    width: 100%;
}

.mapping-field label {
    flex: 1;
    margin-right: 1rem;
    font-weight: bold;
}

.field-dropdown {
    flex: 2;
    width: 60%;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fff;
    transition: border-color 0.3s;
}

.field-dropdown:focus {
    border-color: #4CAF50;
    outline: none;
}

.field-dropdown option {
    padding: 0.5rem;
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

.custom-steps {
    margin-bottom: 2rem;
    justify-content: center;
}

.icon-large {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #4CAF50;
}

.preview-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.preview-table th,
.preview-table td {
    padding: 0.75rem;
    border: 1px solid #ddd;
    text-align: left;
}

.mobile-panel {
    margin: 1rem;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.mobile-panel .flex {
    justify-content: space-between;
    align-items: center;
}

.mobile-buttons .full-width {
    width: 100%;
}

.mobile-buttons .mb-1 {
    margin-bottom: 0.5rem;
}

.p-badge {
    margin-left: 0.5rem;
}

.required {
    color: red;
    margin-left: 4px;
}

</style>
