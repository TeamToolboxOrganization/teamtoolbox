<template>
  <v-app>
    <v-app-bar color="primary" density="comfortable">
      <v-app-bar-title class="font-weight-bold">TeamToolbox Next (Vue + Vuetify)</v-app-bar-title>
      <v-chip color="white" text-color="primary" variant="flat">
        Humeur équipe: {{ state.teamMood }}%
      </v-chip>
    </v-app-bar>

    <v-main class="bg-background">
      <v-container class="py-6" fluid>
        <v-row>
          <v-col cols="12" md="8" class="d-flex flex-column ga-4">
            <PostComposer :channels="channels" @publish="addPost" />

            <v-card rounded="xl" elevation="1" class="pa-4">
              <div class="d-flex flex-wrap ga-3 align-center">
                <v-chip-group v-model="activeChannel" mandatory>
                  <v-chip v-for="channel in channels" :key="channel.id" :value="channel.id">
                    {{ channel.label }}
                  </v-chip>
                </v-chip-group>
                <v-spacer />
                <v-text-field
                  v-model="search"
                  density="compact"
                  hide-details
                  prepend-inner-icon="mdi-magnify"
                  label="Rechercher"
                  variant="outlined"
                  max-width="280"
                />
              </div>
            </v-card>

            <PostFeed :posts="filteredPosts" @comment="addComment" @like="likePost" />
          </v-col>

          <v-col cols="12" md="4" class="d-flex flex-column ga-4">
            <MeetingPanel :meetings="state.meetings" @add="addMeeting" />

            <v-card rounded="xl" elevation="1">
              <v-card-title>Repères manager</v-card-title>
              <v-list lines="three">
                <v-list-item
                  title="1. Prendre le pouls"
                  subtitle="Publiez un message quotidien en canal Quotidien pour suivre les blocages."
                />
                <v-list-item
                  title="2. Formaliser"
                  subtitle="Utilisez un compte-rendu avec décisions et actions après chaque rituel d’équipe."
                />
                <v-list-item
                  title="3. Capitaliser"
                  subtitle="Les conversations et notes restent historisées localement pour conserver le contexte."
                />
              </v-list>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>
  </v-app>
</template>

<script setup>
import MeetingPanel from './components/MeetingPanel.vue'
import PostComposer from './components/PostComposer.vue'
import PostFeed from './components/PostFeed.vue'
import { useWorkspace } from './composables/useWorkspace'

const {
  channels,
  state,
  activeChannel,
  search,
  filteredPosts,
  addPost,
  addComment,
  addMeeting,
  likePost
} = useWorkspace()
</script>
