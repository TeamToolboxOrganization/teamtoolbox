<template>
  <v-card class="presence-card">
    <v-card-title class="d-flex align-center ga-2">
      <v-icon icon="mdi-office-building-marker" color="primary" />
      Présence au bureau
    </v-card-title>

    <v-card-text class="pt-0">
      <div class="d-flex flex-wrap ga-2 mb-3">
        <v-chip color="primary" variant="tonal" prepend-icon="mdi-domain">{{ summary.office }} Bureau</v-chip>
        <v-chip color="teal" variant="tonal" prepend-icon="mdi-home">{{ summary.remote }} Télétravail</v-chip>
        <v-chip color="grey" variant="tonal" prepend-icon="mdi-account-off">{{ summary.away }} Indispo</v-chip>
      </div>

      <v-list density="compact" bg-color="transparent">
        <v-list-item v-for="member in members" :key="member.id" class="px-0">
          <template #prepend>
            <v-avatar size="30" color="blue-grey-lighten-5">
              <span class="text-caption font-weight-bold">{{ member.name.charAt(0) }}</span>
            </v-avatar>
          </template>
          <v-list-item-title>{{ member.name }}</v-list-item-title>
          <v-list-item-subtitle>{{ member.role }}</v-list-item-subtitle>
          <template #append>
            <v-btn-toggle
              :model-value="member.status"
              density="compact"
              divided
              mandatory
              @update:model-value="$emit('update-presence', member.id, $event)"
            >
              <v-btn value="office" size="x-small" icon="mdi-domain" />
              <v-btn value="remote" size="x-small" icon="mdi-home" />
              <v-btn value="away" size="x-small" icon="mdi-account-off" />
            </v-btn-toggle>
          </template>
        </v-list-item>
      </v-list>
    </v-card-text>
  </v-card>
</template>

<script setup>
defineProps({
  members: {
    type: Array,
    default: () => []
  },
  summary: {
    type: Object,
    default: () => ({ office: 0, remote: 0, away: 0 })
  }
})

defineEmits(['update-presence'])
</script>

<style scoped>
.presence-card {
  border: 1px solid #dbeafe;
  background: linear-gradient(180deg, #ffffff, #eff6ff);
}
</style>
