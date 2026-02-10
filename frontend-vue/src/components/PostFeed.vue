<template>
  <div class="d-flex flex-column ga-4">
    <v-card v-for="post in posts" :key="post.id" class="post-card">
      <v-card-item>
        <template #prepend>
          <v-avatar class="avatar" size="40">
            <span class="text-white">{{ post.author.charAt(0) }}</span>
          </v-avatar>
        </template>
        <v-card-title class="text-subtitle-1 font-weight-bold">{{ post.title }}</v-card-title>
        <v-card-subtitle>
          {{ post.author }} · {{ new Date(post.createdAt).toLocaleString('fr-FR') }}
        </v-card-subtitle>
      </v-card-item>

      <v-card-text>
        <p class="mb-2 text-body-1">{{ post.message }}</p>
        <div class="d-flex flex-wrap ga-2">
          <v-chip size="small" color="primary" variant="tonal">{{ post.channel }}</v-chip>
          <v-chip v-for="tag in post.tags" :key="tag" size="small" color="info" variant="outlined">#{{ tag }}</v-chip>
        </div>
      </v-card-text>

      <v-card-actions>
        <v-btn variant="text" color="primary" prepend-icon="mdi-thumb-up-outline" @click="$emit('like', post.id)">
          {{ post.likes }}
        </v-btn>
        <v-spacer />
      </v-card-actions>

      <v-divider />
      <v-list density="compact" class="px-4 py-2 comments" bg-color="transparent">
        <v-list-item v-for="comment in post.comments" :key="comment.id" :title="comment.author" :subtitle="comment.text" />
      </v-list>

      <v-card-actions class="px-4 pb-4">
        <v-text-field
          v-model="comments[post.id]"
          density="compact"
          hide-details
          placeholder="Répondre..."
          variant="outlined"
          bg-color="white"
          @keyup.enter="submitComment(post.id)"
        />
        <v-btn color="secondary" variant="flat" @click="submitComment(post.id)">Envoyer</v-btn>
      </v-card-actions>
    </v-card>

    <v-alert v-if="!posts.length" type="info" variant="tonal" class="mt-2">
      Aucun message ne correspond à vos filtres.
    </v-alert>
  </div>
</template>

<script setup>
import { reactive } from 'vue'

defineProps({
  posts: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['comment', 'like'])
const comments = reactive({})

function submitComment(postId) {
  const text = comments[postId] ?? ''
  emit('comment', postId, 'Vous', text)
  comments[postId] = ''
}
</script>

<style scoped>
.post-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
}

.avatar {
  background: linear-gradient(135deg, #0ea5e9, #2563eb);
}

.comments {
  background: #f8fafc;
}
</style>
