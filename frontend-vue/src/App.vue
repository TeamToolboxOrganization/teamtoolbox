<template>
  <v-app>
    <v-app-bar class="app-bar" density="comfortable" flat>
      <v-app-bar-title class="font-weight-bold d-flex align-center ga-2">
        <v-icon icon="mdi-account-group" />
        TeamToolbox Next
      </v-app-bar-title>
      <v-chip color="success" variant="flat" prepend-icon="mdi-heart-pulse">
        Humeur équipe: {{ state.teamMood }}%
      </v-chip>
    </v-app-bar>

    <v-main class="app-main">
      <v-container class="py-8" fluid>
        <v-row>
          <v-col cols="12" md="8" class="d-flex flex-column ga-4">
            <PostComposer :channels="channels" @publish="addPost" />

            <v-card class="toolbar-card pa-4">
              <div class="d-flex flex-wrap ga-3 align-center">
                <v-chip-group v-model="activeChannel" mandatory>
                  <v-chip
                    v-for="channel in channels"
                    :key="channel.id"
                    :value="channel.id"
                    color="primary"
                    variant="tonal"
                  >
                    {{ channel.label }}
                  </v-chip>
                </v-chip-group>
                <v-spacer />
                <v-text-field
                  v-model="search"
                  density="comfortable"
                  hide-details
                  prepend-inner-icon="mdi-magnify"
                  label="Rechercher"
                  variant="outlined"
                  max-width="300"
                  bg-color="white"
                />
              </div>
            </v-card>

            <PostFeed :posts="filteredPosts" @comment="addComment" @like="likePost" />
          </v-col>

          <v-col cols="12" md="4" class="d-flex flex-column ga-4">
            <OfficePresenceWidget
              :members="orderedTeamPresence"
              :summary="presenceSummary"
              @update-presence="setPresence"
            />

            <MeetingPanel :meetings="state.meetings" @add="addMeeting" />

            <v-card class="manager-card">
              <v-card-title class="d-flex align-center ga-2">
                <v-icon icon="mdi-lightbulb-on-outline" color="warning" />
                Repères manager
              </v-card-title>
              <v-list lines="three" bg-color="transparent">
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
import OfficePresenceWidget from './components/OfficePresenceWidget.vue'
import PostComposer from './components/PostComposer.vue'
import PostFeed from './components/PostFeed.vue'
import { useWorkspace } from './composables/useWorkspace'

const {
  channels,
  state,
  activeChannel,
  search,
  filteredPosts,
  orderedTeamPresence,
  presenceSummary,
  addPost,
  addComment,
  addMeeting,
  likePost,
  setPresence
} = useWorkspace()
</script>

<style scoped>
.app-main {
  background: radial-gradient(circle at top right, #e0e7ff, #f3f6fb 40%, #eef2ff 100%);
  min-height: 100vh;
}

.app-bar {
  background: linear-gradient(135deg, #1e3a8a, #1d4ed8);
  color: #fff;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.toolbar-card {
  backdrop-filter: blur(8px);
  background: rgba(255, 255, 255, 0.88);
}

.manager-card {
  background: linear-gradient(180deg, #ffffff, #f8fafc);
  border: 1px solid #e2e8f0;
}
</style>
