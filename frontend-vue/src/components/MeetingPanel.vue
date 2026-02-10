<template>
  <v-card rounded="xl" elevation="1" class="pa-4">
    <v-card-title class="px-0 text-h6">Échanges formels</v-card-title>

    <v-expansion-panels variant="accordion" class="mb-4">
      <v-expansion-panel v-for="meeting in meetings" :key="meeting.id" rounded="lg">
        <v-expansion-panel-title>
          {{ meeting.title }} - {{ meeting.date }}
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <p class="mb-2">{{ meeting.notes }}</p>
          <strong>Décisions</strong>
          <ul>
            <li v-for="decision in meeting.decisions" :key="decision">{{ decision }}</li>
          </ul>
          <strong>Actions</strong>
          <ul>
            <li v-for="action in meeting.actions" :key="action">{{ action }}</li>
          </ul>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

    <v-divider class="mb-4" />

    <v-text-field v-model="draft.title" label="Nom du rituel" variant="outlined" density="compact" class="mb-2" />
    <v-text-field v-model="draft.date" label="Date" type="date" variant="outlined" density="compact" class="mb-2" />
    <v-textarea v-model="draft.notes" label="Notes" variant="outlined" rows="2" class="mb-2" />
    <v-text-field
      v-model="draft.decisions"
      label="Décisions (séparées par ;)"
      variant="outlined"
      density="compact"
      class="mb-2"
    />
    <v-text-field
      v-model="draft.actions"
      label="Actions (séparées par ;)"
      variant="outlined"
      density="compact"
      class="mb-4"
    />
    <v-btn block color="secondary" :disabled="!draft.title || !draft.date" @click="add">Ajouter un compte-rendu</v-btn>
  </v-card>
</template>

<script setup>
import { reactive } from 'vue'

const props = defineProps({
  meetings: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['add'])

const draft = reactive({
  title: '',
  date: '',
  notes: '',
  decisions: '',
  actions: ''
})

function splitBySemiColon(text) {
  return text
    .split(';')
    .map((item) => item.trim())
    .filter(Boolean)
}

function add() {
  emit('add', {
    title: draft.title,
    date: draft.date,
    notes: draft.notes,
    decisions: splitBySemiColon(draft.decisions),
    actions: splitBySemiColon(draft.actions)
  })

  Object.assign(draft, {
    title: '',
    date: '',
    notes: '',
    decisions: '',
    actions: ''
  })
}
</script>
