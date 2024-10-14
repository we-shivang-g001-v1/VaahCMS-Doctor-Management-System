<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useDoctorStore } from '../../../stores/store-doctors';

const store = useDoctorStore();
const route = useRoute();

// Dialog visibility state
const bulkImportDialogVisible = ref(false);
const selectedFile = ref(null);
const fileError = ref('');

// Open the bulk import dialog
const openBulkImportDialog = () => {
    bulkImportDialogVisible.value = true;
    selectedFile.value = null; // Reset the selected file
    fileError.value = ''; // Reset any error messages
};

// Close the bulk import dialog
const closeBulkImportDialog = () => {
    bulkImportDialogVisible.value = false;
};

// Handle file selection
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file && file.type !== 'text/csv') {
        fileError.value = 'Please upload a valid CSV file.';
        selectedFile.value = null;
    } else {
        fileError.value = ''; // Clear any previous error
        selectedFile.value = file; // Save the selected file
    }
};

// Handle bulk import
const handleBulkImport = async () => {
    if (!selectedFile.value) {
        fileError.value = 'No file selected. Please choose a file.';
        return;
    }

    const formData = new FormData();
    formData.append('file', selectedFile.value); // Append the file to FormData

    try {
        // Replace with your actual API endpoint for file upload
        const response = await store.uploadBulkFile(formData);
        console.log('Upload successful:', response);

        // Optionally handle the response, e.g., show success message
        // Reset the dialog and any errors
        closeBulkImportDialog(); // Close the dialog after processing
    } catch (error) {
        console.error('Error uploading file:', error);
        fileError.value = 'Error uploading file. Please try again.';
    }
};

// Toggle between Filters and Custom Filters
const toggleFilters = (filterType) => {
    if (filterType === 'filters') {
        store.show_filters = !store.show_filters;
        if (store.show_filters) {
            store.show_custom_filters = false;
        }
    } else if (filterType === 'customFilters') {
        store.show_custom_filters = !store.show_custom_filters;
        if (store.show_custom_filters) {
            store.show_filters = false;
        }
    }
};

onMounted(async () => {
    store.getListSelectedMenu();
    store.getListBulkMenu();
});

//--------selected_menu_state
const selected_menu_state = ref();
const toggleSelectedMenuState = (event) => {
    selected_menu_state.value.toggle(event);
};
//--------/selected_menu_state

//--------bulk_menu_state
const bulk_menu_state = ref();
const toggleBulkMenuState = (event) => {
    bulk_menu_state.value.toggle(event);
};
//--------/bulk_menu_state

</script>

<style scoped>
.bulk-import-dialog {
    width: 400px; /* Set your desired width */
}
</style>

<template>
    <div>
        <!-- Actions -->
        <div :class="{'flex justify-content-between': store.isViewLarge()}" class="mt-2 mb-2">

            <!-- Left -->
            <div v-if="store.view === 'large'">

                <!-- Selected Menu -->
                <Button class="p-button-sm"
                        type="button"
                        @click="toggleSelectedMenuState"
                        data-testid="doctors-actions-menu"
                        aria-haspopup="true"
                        aria-controls="overlay_menu">
                    <i class="pi pi-angle-down"></i>
                    <Badge v-if="store.action.items.length > 0" :value="store.action.items.length" />
                </Button>
                <Menu ref="selected_menu_state" :model="store.list_selected_menu" :popup="true" />
                <!-- /Selected Menu -->

            </div>
            <!-- /Left -->

            <!-- Right -->
            <div>
                <div class="grid p-fluid">
                    <div class="col-12">
                        <div class="p-inputgroup ">
                            <InputText v-model="store.query.filter.q"
                                       @keyup.enter="store.delayedSearch()"
                                       class="p-inputtext-sm"
                                       placeholder="Search"/>
                            <Button @click="store.delayedSearch()"
                                    class="p-button-sm"
                                    icon="pi pi-search"/>
                            <Button
                                type="button"
                                class="p-button-sm"
                                :disabled="Object.keys(route.params).length"
                                @click="toggleFilters('filters')">
                                Filters
                                <Badge v-if="store.count_filters > 0" :value="store.count_filters"></Badge>
                            </Button>
                            <Button
                                type="button"
                                class="p-button-sm"
                                :disabled="Object.keys(route.params).length"
                                @click="toggleFilters('customFilters')">
                                Custom Filters
                                <Badge v-if="store.count_custom_filters > 0" :value="store.count_custom_filters"></Badge>
                            </Button>
                            <Button
                                type="button"
                                icon="pi pi-filter-slash"
                                class="p-button-sm"
                                label="Reset"
                                @click="store.resetQuery()" />

                            <!-- Bulk Import Button -->
                            <Button
                                type="button"
                                @click="openBulkImportDialog"
                                class="p-button-sm ml-1"
                                data-testid="doctors-actions-bulk-import-button">
                                Bulk Import
                            </Button>

                            <!-- Bulk Menu -->
                            <Button
                                type="button"
                                @click="toggleBulkMenuState"
                                severity="danger" outlined
                                data-testid="doctors-actions-bulk-menu"
                                aria-haspopup="true"
                                aria-controls="bulk_menu_state"
                                class="ml-1 p-button-sm">
                                <i class="pi pi-ellipsis-v"></i>
                            </Button>
                            <Menu ref="bulk_menu_state" :model="store.list_bulk_menu" :popup="true" />
                            <!-- /Bulk Menu -->

                        </div>
                    </div>
                </div>
            </div>
            <!-- /Right -->

        </div>
        <!-- /Actions -->

        <!-- Bulk Import Dialog -->
        <Dialog header="Bulk Import" v-model:visible="bulkImportDialogVisible" :modal="true" :closable="true" class="bulk-import-dialog">
            <div class="p-fluid">
                <div class="p-field">
                    <label for="file-upload">Upload CSV File</label>
                    <input type="file" id="file-upload" accept=".csv" @change="handleFileChange" class="p-inputtext" />
                    <small class="p-error" v-if="fileError">{{ fileError }}</small>
                </div>
                <div class="p-d-flex p-jc-between">
                    <Button label="Submit" icon="pi pi-check" @click="handleBulkImport" />
                    <Button label="Cancel" icon="pi pi-times" @click="closeBulkImportDialog" class="p-button-secondary" />
                </div>
            </div>
        </Dialog>
        <!-- /Bulk Import Dialog -->

    </div>
</template>
