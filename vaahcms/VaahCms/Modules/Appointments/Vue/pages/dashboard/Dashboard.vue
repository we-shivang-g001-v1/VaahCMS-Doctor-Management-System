<script  setup>
document.title = 'Appointments';

</script>
<template  >

    <div style="margin-top: 8px;">
        <h1 class="text-4xl">Dashboard</h1>
        <div class="grid">
            <div class="col-12 md:col-4">
                <Card title="Total Appointments" class="m-2">
                    <h2>data</h2>
                    <Button label="View Details" class="p-button-sm" />
                </Card>
            </div>
            <div class="col-12 md:col-4">
                <Card title="Completed Appointments" class="m-2">
                    <h2>data</h2>
                    <Button label="View Details" class="p-button-sm" />
                </Card>
            </div>
            <div class="col-12 md:col-4">
                <Card title="Pending Appointments" class="m-2">
                    <h2>data</h2>
                    <Button label="View Details" class="p-button-sm" />
                </Card>
            </div>
        </div>

        <h2 class="text-2xl mt-5">Appointments Overview</h2>
<!--        <Chart type="bar" :data="appointmentsData" class="m-2" />-->
    </div>

</template>


