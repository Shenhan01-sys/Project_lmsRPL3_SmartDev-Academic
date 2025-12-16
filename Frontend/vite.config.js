import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/adminDashboard.css",
                "resources/css/StudentDashboard.css",
                "resources/css/InstructorDashboard.css",
                "resources/css/parentDashboard.css",
                "resources/js/app.js",
                "resources/js/bootstrap.js",
                "resources/js/adminDashboard.js",
                "resources/js/StudentDashboard.js",
                "resources/js/InstructorDashboard.js",
                "resources/js/parentDashboard.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
