import { computed, ref, watch } from 'vue'
import { initialChannels, initialMeetings, initialPosts, initialTeamPresence } from '../data/sampleData'

const STORAGE_KEY = 'teamtoolbox-vue-workspace'

function buildState() {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (!saved) {
    return {
      posts: initialPosts,
      meetings: initialMeetings,
      teamMood: 78,
      teamPresence: initialTeamPresence
    }
  }

  try {
    return JSON.parse(saved)
  } catch {
    return {
      posts: initialPosts,
      meetings: initialMeetings,
      teamMood: 78,
      teamPresence: initialTeamPresence
    }
  }
}

export function useWorkspace() {
  const state = ref(buildState())
  const activeChannel = ref('all')
  const search = ref('')

  watch(
    state,
    () => localStorage.setItem(STORAGE_KEY, JSON.stringify(state.value)),
    { deep: true }
  )

  const filteredPosts = computed(() => {
    return state.value.posts
      .filter((post) => activeChannel.value === 'all' || post.channel === activeChannel.value)
      .filter((post) => {
        if (!search.value) return true

        const haystack = `${post.title} ${post.message} ${post.author} ${post.tags.join(' ')}`.toLowerCase()
        return haystack.includes(search.value.toLowerCase())
      })
      .sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt))
  })

  const presenceSummary = computed(() => {
    return state.value.teamPresence.reduce(
      (summary, member) => {
        summary[member.status] += 1
        return summary
      },
      { office: 0, remote: 0, away: 0 }
    )
  })

  const orderedTeamPresence = computed(() => {
    const order = { office: 0, remote: 1, away: 2 }
    return [...state.value.teamPresence].sort((a, b) => order[a.status] - order[b.status] || a.name.localeCompare(b.name))
  })

  function addPost(payload) {
    state.value.posts.unshift({
      id: crypto.randomUUID(),
      createdAt: new Date().toISOString(),
      likes: 0,
      comments: [],
      ...payload
    })
  }

  function addComment(postId, author, text) {
    const post = state.value.posts.find((item) => item.id === postId)
    if (!post || !text.trim()) return
    post.comments.push({ id: crypto.randomUUID(), author, text })
  }

  function likePost(postId) {
    const post = state.value.posts.find((item) => item.id === postId)
    if (!post) return
    post.likes += 1
  }

  function addMeeting(meeting) {
    state.value.meetings.unshift({
      id: crypto.randomUUID(),
      ...meeting
    })
  }

  function setPresence(memberId, status) {
    if (!['office', 'remote', 'away'].includes(status)) return
    const member = state.value.teamPresence.find((item) => item.id === memberId)
    if (!member) return
    member.status = status
  }

  return {
    channels: initialChannels,
    state,
    activeChannel,
    search,
    filteredPosts,
    presenceSummary,
    orderedTeamPresence,
    addPost,
    addComment,
    addMeeting,
    likePost,
    setPresence
  }
}
