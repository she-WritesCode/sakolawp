<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
// import { embed } from 'embedrax';

const props = defineProps<{
    content: string;
}>();

const videos = ref<any[]>([
]);

const parsedContent = ref('');


const parseContent = () => {
    let contentWithLinks = props.content;

    // Convert URLs in the content to clickable links
    const urlRegex = /(https?:\/\/[^\s]+)/g;

    // Regex patterns for different platforms
    const youtubeRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|.*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
    const tiktokRegex = /(?:https?:\/\/)?(?:www\.)?tiktok\.com\/@.+\/video\/([0-9]+)/;
    const twitterRegex = /(?:https?:\/\/)?(?:www\.)?twitter\.com\/.+\/status\/([0-9]+)/;
    const facebookRegex = /(?:https?:\/\/)?(?:www\.)?facebook\.com\/.+\/videos\/([0-9]+)/;
    const vimeoRegex = /(?:https?:\/\/)?(?:www\.)?vimeo\.com\/([0-9]+)/;
    const dailymotionRegex = /(?:https?:\/\/)?(?:www\.)?dailymotion\.com\/video\/([a-zA-Z0-9]+)/;
    const googleDriveRegex = /(?:https?:\/\/)?(?:www\.)?drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)\/view/;

    contentWithLinks = contentWithLinks.replace(urlRegex, (url) => {
        let embed = ''
        if (youtubeRegex.test(url)) {
            const match = youtubeRegex.exec(url);
            if (match) {
                const videoId = match[1];
                embed = `<iframe class="embed-youtube" width="560" height="315" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe>`;
            }
        } else if (tiktokRegex.test(url)) {
            const match = tiktokRegex.exec(url);
            if (match) {
                const videoId = match[1];
                embed = `<blockquote class="embed-tiktok" cite="${url}" data-video-id="${videoId}"></blockquote>`;
            }
        } else if (twitterRegex.test(url)) {
            const match = twitterRegex.exec(url);
            if (match) {
                const videoId = match[1];
                embed = `<blockquote  class="embed-twitter"><a href="${url}"></a></blockquote>`;
            }
        } else if (facebookRegex.test(url)) {
            const match = facebookRegex.exec(url);
            if (match) {
                const videoId = match[1];
                embed = `<iframe  class="embed-facebook" src="https://www.facebook.com/plugins/video.php?href=${encodeURIComponent(url)}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
            }
        } else if (vimeoRegex.test(url)) {
            const match = vimeoRegex.exec(url);
            if (match) {
                const videoId = match[1];
                embed = `<iframe  class="embed-vimeo" src="https://player.vimeo.com/video/${videoId}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
            }
        } else if (dailymotionRegex.test(url)) {
            const match = dailymotionRegex.exec(url);
            if (match) {
                const videoId = match[1];
                embed = `<iframe  class="embed-dailymotion" src="https://www.dailymotion.com/embed/video/${videoId}" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
            }
        } else if (googleDriveRegex.test(url)) {
            const match = googleDriveRegex.exec(url);
            if (match) {
                const fileId = match[1];
                embed = `<iframe class="embed-google-drive" src="https://drive.google.com/file/d/${fileId}/preview" width="560" height="315" frameborder="0" allowfullscreen></iframe>`;
            }
        }
        const urltag = `<a href="${url}" target="_blank">${url}</a>`
        return embed ? `${embed}<br/>${urltag}` : urltag;
    });

    parsedContent.value = contentWithLinks;
};



onMounted(() => {
    parseContent();
});

watch(() => props.content, () => {
    parseContent();
});
watch(videos, () => {
});

</script>

<template>
    <div v-html="parsedContent"></div>
</template>

<style>
.embed-tiktok {
    display: block;
    position: relative;
    width: 100%;
    max-width: 370px;
    max-height: 560px;
    float: left;
}

.embed-twitter {
    display: block;
    position: relative;
    width: 100%;
    max-width: 300px;
    float: left;
}

.embed-youtube {
    position: relative;
    display: block;
    width: 100%;
    max-width: 640px;
    max-height: 360px;
    /* Allow the height to adjust proportionally */
    float: left;
}

.embed-facebook {
    display: block;
    position: relative;
    width: 100%;
    max-width: 318px;
    max-height: auto;
    /* Allow the height to adjust proportionally */
    float: left;
}

.embed-facebook2 {
    display: block;
    position: relative;
    width: 100%;
    max-width: 318px;
    max-height: auto;
    /* Allow the height to adjust proportionally */
    float: left;
}

.embed-vimeo {
    display: block;
    /* You can assign any css properties */
}

.embed-dailymotion {
    display: block;
    /* You can assign any css properties */
}

.embed-google-drive {
    display: block;
    /* You can assign any css properties */
}
</style>
