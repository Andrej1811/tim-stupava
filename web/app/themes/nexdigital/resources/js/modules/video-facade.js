/**
 * Video facade.
 *
 * A candidate's video vizitka renders as a poster with a play button, whether
 * the video is a YouTube link or a file from the media library. Nothing is
 * requested until the visitor clicks: the source lives in a data attribute, and
 * only then does a player replace the poster. For an embed that keeps a page
 * view from talking to a third country on its own; for an uploaded file it
 * keeps a 30 MB download off every visit.
 */

/** Uploaded file: a native player, no third party involved. */
function fileTag(src, title) {
    const video = document.createElement("video");
    video.src = src;
    video.title = title;
    video.className = "absolute inset-0 h-full w-full bg-black object-contain";
    video.controls = true;
    video.autoplay = true;
    video.playsInline = true;

    return video;
}

/** YouTube or Vimeo: the player URL already carries autoplay. */
function embedTag(src, title) {
    const frame = document.createElement("iframe");
    frame.src = src;
    frame.title = title;
    frame.className = "absolute inset-0 h-full w-full";
    frame.allow = "accelerometer; autoplay; encrypted-media; picture-in-picture; fullscreen";
    frame.allowFullscreen = true;
    frame.referrerPolicy = "strict-origin-when-cross-origin";

    return frame;
}

export function initVideoFacade() {
    document.querySelectorAll("[data-video-play], [data-video-file]").forEach((button) => {
        button.addEventListener("click", () => {
            const facade = button.closest("[data-video-facade]");
            const file = button.dataset.videoFile;
            const embed = button.dataset.videoPlay;

            if (!facade || (!file && !embed)) {
                return;
            }

            const title = button.dataset.videoTitle || "";
            const player = file ? fileTag(file, title) : embedTag(embed, title);

            facade.replaceChildren(player);
            // The poster and its button are gone, so move focus into the player
            // rather than losing it on a removed element.
            player.focus();
        });
    });
}
