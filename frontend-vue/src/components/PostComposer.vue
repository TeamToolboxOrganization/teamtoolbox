<template>
  <v-card class="composer pa-4">
    <v-card-title class="text-h6 px-0 d-flex align-center ga-2">
      <v-icon icon="mdi-pencil" color="primary" />
      Nouveau message d'équipe
    </v-card-title>
    <v-text-field v-model="draft.title" label="Titre" density="comfortable" variant="outlined" class="mb-2" bg-color="white" />
    <v-textarea
      v-model="draft.message"
      label="Message"
      auto-grow
      variant="outlined"
      rows="3"
      class="mb-2"
      bg-color="white"
    />

    <div class="d-flex flex-wrap ga-2 mb-4">
      <v-chip-group v-model="channel" mandatory>
        <v-chip
          v-for="item in channels.filter((c) => c.id !== 'all')"
          :key="item.id"
          :value="item.id"
          color="secondary"
          variant="tonal"
        >
          {{ item.label }}
        </v-chip>
      </v-chip-group>
    </div>

    <div class="d-flex justify-end">
      <v-btn color="primary" :disabled="!isValid" prepend-icon="mdi-send" @click="submit">Publier</v-btn>
    </div>
  </v-card>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'

defineProps({
  channels: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['publish'])

const channel = ref('daily')
const draft = reactive({
  title: '',
  message: ''
})

const isValid = computed(() => draft.title.trim() && draft.message.trim())

function submit() {
  emit('publish', {
    author: 'Vous',
    channel: channel.value,
    title: draft.title.trim(),
    message: draft.message.trim(),
    tags: channel.value === 'formal' ? ['compte-rendu'] : ['discussion']
  })

  draft.title = ''
  draft.message = ''
  channel.value = 'daily'
}
</script>

<style scoped>
.composer {
  background: linear-gradient(180deg, #ffffff, #f8fafc);
  border: 1px solid #e2e8f0;
}
</style>
