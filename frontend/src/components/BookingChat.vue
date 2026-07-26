<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { apiBookingMessages, apiSendBookingMessage, ApiError } from '../api/client'

const props = defineProps({
  bookingId: { type: [Number, String], required: true },
  enabled: { type: Boolean, default: false },
})

const messages = ref([])
const body = ref('')
const loading = ref(false)
const sending = ref(false)
const error = ref('')
const listRef = ref(null)
let pollTimer = null

const canChat = computed(() => props.enabled)

async function load() {
  if (!canChat.value) return
  loading.value = true
  error.value = ''
  try {
    const res = await apiBookingMessages(props.bookingId)
    messages.value = res.data || []
    await nextTick()
    scrollBottom()
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : 'Failed to load chat'
  } finally {
    loading.value = false
  }
}

async function refresh() {
  if (!canChat.value) return
  try {
    const res = await apiBookingMessages(props.bookingId)
    messages.value = res.data || []
    await nextTick()
    scrollBottom()
  } catch {
    // ignore poll errors
  }
}

function scrollBottom() {
  if (listRef.value) listRef.value.scrollTop = listRef.value.scrollHeight
}

async function send() {
  const text = body.value.trim()
  if (!text || sending.value) return
  sending.value = true
  error.value = ''
  try {
    const res = await apiSendBookingMessage(props.bookingId, text)
    messages.value.push(res.message)
    body.value = ''
    await nextTick()
    scrollBottom()
  } catch (err) {
    error.value = err instanceof ApiError ? err.message : 'Failed to send'
  } finally {
    sending.value = false
  }
}

function startPoll() {
  stopPoll()
  if (!canChat.value) return
  pollTimer = setInterval(refresh, 8000)
}

function stopPoll() {
  if (pollTimer) {
    clearInterval(pollTimer)
    pollTimer = null
  }
}

watch(
  () => [props.bookingId, props.enabled],
  () => {
    messages.value = []
    if (canChat.value) {
      load()
      startPoll()
    } else {
      stopPoll()
    }
  },
  { immediate: true },
)

onMounted(() => {
  if (canChat.value) startPoll()
})
onUnmounted(stopPoll)
</script>

<template>
  <div class="bk-chat">
    <div class="bk-chat-head">
      <h3>Booking chat</h3>
      <p v-if="canChat">Message the other party about this job</p>
      <p v-else>Chat unlocks after the customer accepts the provider quotation.</p>
    </div>

    <div v-if="!canChat" class="bk-chat-locked">
      Accept the deal to start chatting.
    </div>

    <template v-else>
      <p v-if="error" class="auth-alert">{{ error }}</p>
      <div ref="listRef" class="bk-chat-list">
        <div v-if="loading && !messages.length" class="db-empty" style="padding: 20px;">Loading messages…</div>
        <div v-else-if="!messages.length" class="db-empty" style="padding: 20px;">No messages yet. Say hello.</div>
        <div
          v-for="msg in messages"
          :key="msg.id"
          class="bk-chat-bubble"
          :class="{ mine: msg.mine }"
        >
          <div class="meta">{{ msg.user?.name || 'User' }} · {{ new Date(msg.created_at).toLocaleString() }}</div>
          <div class="text">{{ msg.body }}</div>
        </div>
      </div>
      <form class="bk-chat-compose" @submit.prevent="send">
        <input v-model="body" type="text" maxlength="2000" placeholder="Type a message…" />
        <button type="submit" class="db-btn db-btn-primary" :disabled="sending || !body.trim()">
          {{ sending ? '…' : 'Send' }}
        </button>
      </form>
    </template>
  </div>
</template>
