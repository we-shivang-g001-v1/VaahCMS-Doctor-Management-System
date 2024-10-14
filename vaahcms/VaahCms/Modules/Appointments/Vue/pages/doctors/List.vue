<script setup>
import {onMounted, reactive, ref} from "vue";
import {useRoute} from 'vue-router';

import {useDoctorStore} from '../../stores/store-doctors'
import {useRootStore} from '../../stores/root'

import Actions from "./components/Actions.vue";
import Table from "./components/Table.vue";
import Filters from './components/Filters.vue'
import CustomFilter from './components/CustomFilter.vue'

const store = useDoctorStore();
const root = useRootStore();
const route = useRoute();

import { useConfirm } from "primevue/useconfirm";

const confirm = useConfirm();


onMounted(async () => {
    document.title = 'Doctors - Appointments';
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
const exportDoctors = () => {
    store.exportDoctors();
}
const importDoctors = (jsonData) => {
    store.importDoctors(jsonData);
}

</script>
<template>

    <div class="grid" v-if="store.assets">

        <div :class="'col-' + ((store.show_filters || store.show_custom_filters) ? 9 : store.list_view_width)">

        <Panel class="is-small">

                <template class="p-1" #header>

                    <div class="flex flex-row">
                        <div >
                            <b class="mr-1">Doctors</b>
                            <Badge v-if="store.list && store.list.total > 0"
                                   :value="store.list.total">
                            </Badge>
                        </div>

                    </div>

                </template>

                <template #icons>

                    <div class="p-inputgroup">



                        <Button @click="openFileDialog">Upload Doctors CSV</Button>
                        <input type="file" ref="fileInput" @change="handleFileUpload" accept=".csv" style="display: none;" />
                        <Button label="Export Doctors"
                                @click="exportDoctors"
                                style="margin-left: 2px;"
                        />
                    <Button
                        data-testid="doctors-list-create"
                            class="p-button-sm"
                            @click="store.toForm()">
                        <i class="pi pi-plus mr-1"></i>
                        Create
                    </Button>


                    <Button data-testid="doctors-list-reload"
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
                        data-testid="doctors-create-menu"
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
        <CustomFilter/>

        <RouterView/>

    </div>


</template>
