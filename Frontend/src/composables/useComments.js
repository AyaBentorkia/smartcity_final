import { ref } from 'vue'
import {
  addComment,
  updateComment,
  deleteComment,
  getCommentsByIncidentForMunicipalAdmin,
  getCommentsByIncidentForAgent,
} from '../api/CommentApi'
import { useAuthStore } from '../stores/AuthStore'
import { storeToRefs } from 'pinia'
import { UserRole } from '../constants/UserRole'

const comments = ref([])
const commentsLoading = ref(false)
const commentActionLoading = ref(false)
const commentError = ref(null)


export function useComments() {
  const authStore = storeToRefs(useAuthStore());

const { userRole } = authStore;
  const fetchComments = async (incidentId) => {
    commentsLoading.value = true
    commentError.value = null
    console.log('incident id : ',incidentId)
    try {
      let response ;
      if(userRole.value===UserRole.AGENT) response= await getCommentsByIncidentForAgent(incidentId);
      else if(userRole.value===UserRole.ADMIN_MUNICIPAL) response= await getCommentsByIncidentForMunicipalAdmin(incidentId);
    console.log('data : ',response)
      comments.value = response.data.data
    } catch (err) {
      console.log("erreur : ",err)
      commentError.value = err.response?.data?.message || 'Erreur lors du chargement des commentaires'
    } finally {
      commentsLoading.value = false
    }
  }

  const AddComment = async (incidentId, content) => {
    if (!content?.trim()) return
    commentActionLoading.value = true
    commentError.value = null
    try {
      const response = await addComment(incidentId, content)
      comments.value.unshift(response.data.data) // ajout en tête comme un feed
      return response.data.data
    } catch (err) {
      commentError.value = err.response?.data?.message || "Erreur lors de l'ajout"
    } finally {
      commentActionLoading.value = false
    }
  }

  const UpdateComment = async (commentId, content) => {
    if (!content?.trim()) return
    commentActionLoading.value = true
    commentError.value = null
    try {
      const response = await updateComment(commentId, content)
      const updated = response.data.data
      const index = comments.value.findIndex((c) => c.id === commentId)
      if (index !== -1) comments.value.splice(index, 1, updated)
      return updated
    } catch (err) {
      commentError.value = err.response?.data?.message || 'Erreur lors de la modification'
    } finally {
      commentActionLoading.value = false
    }
  }

  const DeleteComment = async (commentId) => {
    commentActionLoading.value = true
    commentError.value = null
    try {
      await deleteComment(commentId)
      comments.value = comments.value.filter((c) => c.id !== commentId)
    } catch (err) {
      commentError.value = err.response?.data?.message || 'Erreur lors de la suppression'
    } finally {
      commentActionLoading.value = false
    }
  }

  const resetComments = () => {
    comments.value = []
    commentError.value = null
  }

  return {
    comments,
    commentsLoading,
    commentActionLoading,
    commentError,
    fetchComments,
    AddComment,
    UpdateComment,
    DeleteComment,
    resetComments,
  }
}