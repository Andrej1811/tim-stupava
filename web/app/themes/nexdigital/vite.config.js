import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import { writeFileSync, rmSync } from "node:fs";
import { resolve } from "node:path";

/**
 * Public URL where the built assets live, relative to WP_HOME.
 * Bedrock serves wp-content from /app, themes live under /app/themes.
 */
const PUBLIC_BASE = "/app/themes/nexdigital/public/build/";

const DEV_HOST = "localhost";
const DEV_PORT = 5173;
const DEV_URL = `http://${DEV_HOST}:${DEV_PORT}`;
const HOT_FILE = resolve(__dirname, "public/hot");

/**
 * Writes a `hot` file so PHP knows the dev server is running, and reloads
 * the browser when a .php template changes.
 */
function wordpressHmr() {
    return {
        name: "nexdigital-wp-hmr",
        apply: "serve",
        configureServer(server) {
            writeFileSync(HOT_FILE, DEV_URL);

            const clean = () => {
                try {
                    rmSync(HOT_FILE, { force: true });
                } catch {}
            };

            process.on("exit", clean);
            process.on("SIGINT", () => process.exit());
            process.on("SIGTERM", () => process.exit());
            server.httpServer?.once("close", clean);
        },
        handleHotUpdate({ file, server }) {
            if (file.endsWith(".php")) {
                server.ws.send({ type: "full-reload", path: "*" });
                return [];
            }
        },
    };
}

export default defineConfig(({ command }) => ({
    base: command === "build" ? PUBLIC_BASE : "/",
    publicDir: false,
    plugins: [tailwindcss(), wordpressHmr()],
    build: {
        outDir: "public/build",
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, "resources/js/app.js"),
                admin: resolve(__dirname, "resources/css/admin.css"),
            },
        },
    },
    server: {
        host: DEV_HOST,
        port: DEV_PORT,
        strictPort: true,
        cors: true,
        origin: DEV_URL,
        watch: {
            // Reload when PHP templates change.
            ignored: ["!**/*.php"],
        },
    },
}));
