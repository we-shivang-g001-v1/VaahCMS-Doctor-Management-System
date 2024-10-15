<script setup>
import { onMounted, reactive, ref } from "vue";
import { useRoute } from "vue-router";

import { useDoctorStore } from '../../stores/store-doctors';
import { useRootStore } from '../../stores/root';

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue';
import CustomFilter from './components/CustomFilter.vue';

import { useConfirm } from "primevue/useconfirm";
import Dialog from "primevue/dialog";

const store = useDoctorStore();
const root = useRootStore();
const route = useRoute();
const confirm = useConfirm();

//--------modal control
const isModalVisible = ref(false);

//---------File Input reference and CSV handling
const fileInput = ref(null);
const openFileDialog = () => {
    fileInput.value.click();
};
const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const contents = e.target.result;
            const jsonData = csvToJson(contents);
            console.log('Parsed JSON data:', jsonData);
            importDoctors(jsonData);
            isModalVisible.value = false; // close modal after file upload
        };
        reader.readAsText(file);
    }
};
const csvToJson = (csv) => {
    const lines = csv.split('\n');
    const result = [];
    const headers = lines[0].split(',');
    for (let i = 1; i < lines.length; i++) {
        const obj = {};
        const currentLine = lines[i].split(',');
        for (let j = 0; j < headers.length; j++) {
            obj[headers[j].trim()] = currentLine[j] ? currentLine[j].trim() : '';
        }
        result.push(obj);
    }
    return result;
};
const importDoctors = (jsonData) => {
    store.importDoctors(jsonData);
}

onMounted(async () => {
    document.title = 'Doctors - Appointments';
    store.item = null;

    await store.onLoad(route);
    await store.watchRoutes(route);
    await store.watchStates();
    await store.getAssets();
    await store.getList();
    await store.getListCreateMenu();
});

const exportDoctors = () => {
    store.exportDoctors();
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
                    </div>
                </template>

                <Actions />
                <Table />
            </Panel>
        </div>

        <Filters />
        <CustomFilter />

        <!-- File Upload Modal -->
        <!-- File Upload Modal -->
        <Dialog
            v-model:visible="isModalVisible"
            header="Upload a File"
            :modal="true"
            :closable="true"
            :dismissableMask="true"
            class="custom-file-upload-modal"
        >
            <div class="file-upload-modal-content">
                <i class="pi pi-upload icon-large"></i>
                <p class="modal-text">
                    Select a CSV file to upload doctors' data from your computer.
                </p>

                <div class="upload-button-container">
                    <input
                        type="file"
                        ref="fileInput"
                        @change="handleFileUpload"
                        accept=".csv"
                        class="hidden-file-input"
                    />
                    <Button
                        label="Choose File"
                        icon="pi pi-file"
                        class="p-button-rounded p-button-outlined"
                        @click="openFileDialog"
                    />
                </div>

                <div class="footer-note">
                    <p>Supported format: .csv | Max size: 5MB</p>
                </div>
            </div>
        </Dialog>

        <RouterView />
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

.modal-text {
    margin: 1rem 0;
    font-size: 1.2rem;
    color: #333;
}

.upload-button-container {
    margin: 1rem 0;
}

.hidden-file-input {
    display: none;
}

.footer-note {
    font-size: 0.85rem;
    color: #888;
}
</style>
