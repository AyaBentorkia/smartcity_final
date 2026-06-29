import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { getNotifications, markAsRead as markAsReadApi, markAllAsRead as markAllAsReadApi } from '../api/NotificationApi'

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([])
  const loading       = ref(false)
  const fetched       = ref(false)
  let   _echoChannel  = null   // référence au canal — empêche le double bind

  const unreadCount = computed(() =>
    notifications.value.filter(n => !n.read_at).length
  )

  // N'appelle l'API que si on n'a pas encore chargé
  async function fetchIfNeeded() {
    if (fetched.value) return
    loading.value = true
    try {
      const { data } = await getNotifications()
      notifications.value = Array.isArray(data?.data) ? data.data : []
      fetched.value = true
    } catch (e) {
      console.error('Erreur notifications:', e)
      notifications.value = []
    } finally {
      loading.value = false
    }
  }

  // Force le rechargement (ex: après markAllRead)
  async function refresh() {
    fetched.value = false
    await fetchIfNeeded()
  }

  async function markAsRead(notif) {
    if (notif.read_at) return
    try {
      await markAsReadApi(notif.id)
      notif.read_at = new Date().toISOString()
    } catch (e) {
      console.error('Erreur markAsRead', e)
    }
  }

  async function markAllRead() {
    try {
      await markAllAsReadApi()
      notifications.value.forEach(n => (n.read_at = new Date().toISOString()))
    } catch (e) {
      console.error('Erreur markAllRead', e)
    }
  }

  // Appelé par les events WebSocket — pas de refetch réseau
  function pushNotification(notif) {
    notifications.value.unshift(notif)
  }

  // ── Appelé UNE SEULE FOIS après login ──────────────────────────────────
  function initEcho(userId) {
    if (_echoChannel) return   // déjà abonné, on ne rebind pas

    _echoChannel = window.Echo.private(`user.${userId}`)

    _echoChannel.listen('.incident.created', (payload) => {
      pushNotification({
        id:          payload.notification_id,
        incident_id: payload.id,
        title:       'Nouvel incident signalé',
        body:        `${payload.category ?? ''} — zone : ${payload.zone ?? ''}`,
        read_at:     null,
        created_at:  payload.reported_at ?? new Date().toISOString(),
      })
    })

    _echoChannel.listen('.incident.assigned', (payload) => {
      pushNotification({
        id:            payload.notification_id,
        incident_id:   payload.incident_id,
        assignment_id: payload.assignment_id,
        title:         'Incident assigné',
        body:          `${payload.category ?? ''} — zone : ${payload.zone ?? ''}`,
        read_at:       null,
        created_at:    payload.assigned_at ?? new Date().toISOString(),
      })
    })
  }

  // ── Appelé UNE SEULE FOIS au logout ────────────────────────────────────
  function leaveEcho(userId) {
    if (!_echoChannel) return
    window.Echo.leave(`user.${userId}`)
    _echoChannel = null
  }

  // Réinitialise au logout
  function $reset() {
    notifications.value = []
    fetched.value       = false
    loading.value       = false
    // _echoChannel géré uniquement par leaveEcho()
  }

  return {
    notifications, loading, unreadCount,
    fetchIfNeeded, refresh, markAsRead, markAllRead,
    pushNotification, initEcho, leaveEcho, $reset,
  }
})