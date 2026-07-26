<script setup>
</script>

<template>
  <div class="db-panel">
    <div class="db-panel-head">
      <div>
        <h2>My jobs</h2>
        <p>Assigned jobs appear here after verification and activation</p>
      </div>
    </div>
    <div class="db-panel-body">
      <div class="db-empty">
        No assigned jobs yet.
        <div style="margin-top: 12px;">
          <RouterLink to="/dashboard/provider/requests" class="db-btn db-btn-primary">View booking requests</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>
