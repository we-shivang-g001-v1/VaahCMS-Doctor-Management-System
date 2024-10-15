<script src="../../stores/store-appointments.js"></script>
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
            importAppointments(jsonData);
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
const importAppointments = (jsonData) => {
    store.importAppointments(jsonData);
}


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

const exportAppointments = () => {
    console.log("hello");
    store.exportAppointments();
};
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
                    Select a CSV file to upload Appointment' data from your computer.
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

