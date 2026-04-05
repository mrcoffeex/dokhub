<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    role: 'guest' | 'doctor' | 'admin';
    botName?: string;
}>();

const name = computed(() => props.botName ?? 'Dokie');

interface Message {
    from: 'user' | 'bot';
    text: string;
}

const open        = ref(false);
const loading     = ref(false);
const input       = ref('');
const messagesEl  = ref<HTMLElement | null>(null);
const limitReached = ref(false);

const greetings = computed<Record<string, string>>(() => ({
    guest:  `Hi! I'm ${name.value}. I can help you find a doctor, understand the booking process, or answer general health questions. How can I help? Please note that sometimes I might get things wrong, so always double-check critical information!`,
    doctor: `Hello, Doctor! I have access to your upcoming appointments and recent patients. Ask me to summarise a patient, draft clinical notes, or anything else you need. Please note that sometimes I might get things wrong, so always double-check critical information!`,
    admin:  `Hi! I'm your ${name.value} platform assistant. I can help you interpret analytics, summarise platform activity, or answer operational questions.`,
}));

const messages = ref<Message[]>([
    { from: 'bot', text: greetings.value[props.role] },
]);

/* ───────── accent colours per role ───────── */
const accent: Record<string, string> = {
    guest:  'bg-blue-600',
    doctor: 'bg-emerald-600',
    admin:  'bg-violet-600',
};
const accentHover: Record<string, string> = {
    guest:  'hover:bg-blue-700',
    doctor: 'hover:bg-emerald-700',
    admin:  'hover:bg-violet-700',
};
const accentBubble: Record<string, string> = {
    guest:  'bg-blue-600 text-white',
    doctor: 'bg-emerald-600 text-white',
    admin:  'bg-violet-600 text-white',
};

async function send() {
    const text = input.value.trim();
    if (!text || loading.value || limitReached.value) return;

    messages.value.push({ from: 'user', text });
    input.value = '';
    loading.value = true;
    await scrollBottom();

    try {
        const { data } = await axios.post('/ai/chat', { message: text, role: props.role });
        messages.value.push({ from: 'bot', text: data.reply });
    } catch (err: unknown) {
        const axiosErr = err as { response?: { data?: { reply?: string; limit_reached?: boolean } } };
        const data = axiosErr?.response?.data;
        if (data?.limit_reached) {
            limitReached.value = true;
        }
        const reply = data?.reply ?? 'Sorry, something went wrong. Please try again.';
        messages.value.push({ from: 'bot', text: reply });
    } finally {
        loading.value = false;
        await scrollBottom();
    }
}

async function clearHistory() {
    await axios.post('/ai/chat/clear', { role: props.role });
    messages.value = [{ from: 'bot', text: greetings.value[props.role] }];
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        send();
    }
}

async function scrollBottom() {
    await nextTick();
    if (messagesEl.value) {
        messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
    }
}

/* ── simple markdown-ish renderer: bold, line breaks, bullet points ── */
function renderText(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^[-•] (.+)$/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>(\n|$))+/g, (m) => `<ul class="list-disc pl-4 space-y-0.5">${m}</ul>`)
        .replace(/\n/g, '<br>');
}
</script>

<template>
    <!-- Floating trigger button -->
    <button
        type="button"
        :class="[
            'fixed bottom-5 right-5 z-50 flex h-14 w-14 items-center justify-center rounded-full shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
            accent[role],
            accentHover[role],
            open ? 'scale-0 opacity-0 pointer-events-none' : 'scale-100 opacity-100',
        ]"
        aria-label="Open AI chat"
        @click="open = true"
    >
        <!-- Sparkle / chat icon -->
        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
        </svg>
    </button>

    <!-- Chat panel -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-95"
    >
        <div
            v-if="open"
            class="fixed bottom-5 right-5 z-50 flex w-[360px] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-900"
            style="height: 520px;"
        >
            <!-- Header -->
            <div :class="['flex shrink-0 items-center justify-between px-4 py-3', accent[role]]">
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                    </svg>
                    <span class="text-sm font-semibold text-white">{{ name }}</span>
                    <span class="rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-medium text-white uppercase tracking-wide">{{ role }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <!-- Clear button -->
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-white/70 transition hover:bg-white/20 hover:text-white"
                        title="Clear conversation"
                        @click="clearHistory"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                    <!-- Close button -->
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-white/70 transition hover:bg-white/20 hover:text-white"
                        aria-label="Close chat"
                        @click="open = false"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages -->
            <div
                ref="messagesEl"
                class="flex flex-1 flex-col gap-3 overflow-y-auto px-4 py-4"
            >
                <div
                    v-for="(msg, i) in messages"
                    :key="i"
                    :class="['flex', msg.from === 'user' ? 'justify-end' : 'justify-start']"
                >
                    <!-- Bot avatar -->
                    <div v-if="msg.from === 'bot'" class="mr-2 mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                    </div>

                    <div
                        :class="[
                            'max-w-[80%] rounded-2xl px-3 py-2 text-sm leading-relaxed',
                            msg.from === 'user'
                                ? accentBubble[role]
                                : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                            msg.from === 'user' ? 'rounded-br-sm' : 'rounded-bl-sm',
                        ]"
                        v-html="renderText(msg.text)"
                    />
                </div>

                <!-- Typing indicator -->
                <div v-if="loading" class="flex justify-start">
                    <div class="mr-2 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-700">
                        <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                        </svg>
                    </div>
                    <div class="flex items-center gap-1 rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-3 dark:bg-gray-800">
                        <span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 dark:bg-gray-500" style="animation-delay:0ms"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 dark:bg-gray-500" style="animation-delay:150ms"></span>
                        <span class="h-2 w-2 animate-bounce rounded-full bg-gray-400 dark:bg-gray-500" style="animation-delay:300ms"></span>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="shrink-0 border-t border-gray-200 bg-white px-3 py-3 dark:border-gray-700 dark:bg-gray-900">
                <!-- Rate-limit banner (guest only) -->
                <div
                    v-if="limitReached"
                    class="mb-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-center text-xs text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-300"
                >
                    <svg class="mx-auto mb-1 h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Hourly limit reached. Chat will be available again in up to 1 hour.
                </div>

                <div
                    v-if="!limitReached"
                    class="flex items-end gap-2 rounded-xl border border-gray-300 bg-gray-50 px-3 py-2 focus-within:border-gray-400 dark:border-gray-600 dark:bg-gray-800 dark:focus-within:border-gray-500"
                >
                    <textarea
                        v-model="input"
                        rows="1"
                        placeholder="Ask anything…"
                        class="flex-1 resize-none bg-transparent text-sm text-gray-900 outline-none placeholder:text-gray-400 dark:text-white dark:placeholder:text-gray-500"
                        style="max-height: 80px; overflow-y: auto;"
                        :disabled="loading"
                        @keydown="onKeydown"
                        @input="($event.target as HTMLTextAreaElement).style.height = 'auto'; ($event.target as HTMLTextAreaElement).style.height = ($event.target as HTMLTextAreaElement).scrollHeight + 'px'"
                    />
                    <button
                        type="button"
                        :disabled="!input.trim() || loading"
                        :class="[
                            'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition disabled:opacity-40',
                            accent[role],
                            accentHover[role],
                        ]"
                        @click="send"
                    >
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                    </button>
                </div>
                <p class="mt-1.5 text-center text-[10px] text-gray-400 dark:text-gray-600">Powered by Gemini AI · May make mistakes</p>
            </div>
        </div>
    </Transition>
</template>
