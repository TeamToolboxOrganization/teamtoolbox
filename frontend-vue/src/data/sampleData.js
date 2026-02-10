export const initialChannels = [
  { id: 'all', label: 'Tous' },
  { id: 'daily', label: 'Quotidien' },
  { id: 'formal', label: 'Formel' },
  { id: 'alerts', label: 'Alertes' }
]

export const initialPosts = [
  {
    id: crypto.randomUUID(),
    author: 'Camille (Manager)',
    channel: 'formal',
    createdAt: '2026-02-10T08:30:00.000Z',
    title: 'Rituel hebdo : objectif sprint #7',
    message:
      'Nous visons une réduction du délai de réponse client de 20%. Merci de remonter les blocages dès aujourd\'hui.',
    tags: ['objectif', 'sprint'],
    likes: 7,
    comments: [
      { id: crypto.randomUUID(), author: 'Nora', text: 'Je prends le suivi SLA support.' },
      { id: crypto.randomUUID(), author: 'Louis', text: 'Je prépare un dashboard d’ici vendredi.' }
    ]
  },
  {
    id: crypto.randomUUID(),
    author: 'Yanis',
    channel: 'daily',
    createdAt: '2026-02-10T10:15:00.000Z',
    title: 'Retour client ACME',
    message: 'Le client est satisfait de la livraison, mais demande plus de visibilité sur la roadmap.',
    tags: ['client', 'feedback'],
    likes: 4,
    comments: []
  },
  {
    id: crypto.randomUUID(),
    author: 'Sofia RH',
    channel: 'alerts',
    createdAt: '2026-02-10T07:45:00.000Z',
    title: 'Rappel : entretiens individuels',
    message: 'Les entretiens trimestriels sont à programmer avant le 25.',
    tags: ['rh'],
    likes: 2,
    comments: []
  }
]

export const initialMeetings = [
  {
    id: crypto.randomUUID(),
    title: '1:1 Manager / Équipe',
    date: '2026-02-11',
    notes: 'Focus sur la charge mentale et la priorisation.',
    decisions: ['Limiter à 2 sujets bloquants maximum par personne'],
    actions: ['Camille : arbitrer backlog avant jeudi', 'Nora : proposer nouveau template CR']
  }
]
