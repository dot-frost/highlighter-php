<template>
    <slot v-bind="{ isPlaying, activeVoice, player, play, stop }"></slot>
</template>

<script>
export default {
    name: "Voice",
    props: {
        voices: {
            type: Object,
            required: true
        },
    },
    data() {
        return {
            player: null,
            isPlaying: false,
            activeVoice: null,
        }
    },
    methods: {
        play(voice) {
            this.activeVoice = voice;
            this.player.src = this.voices[voice];
            this.player.play();
            this.isPlaying = true;
        },
        stop() {
            this.player.pause();
            this.isPlaying = false;
        },
        onEnded() {
            this.isPlaying = false;
        },
    },
    mounted() {
        this.player = new Audio();
        this.player.addEventListener('ended', this.onEnded);
    },
}
</script>

<style scoped>

</style>
