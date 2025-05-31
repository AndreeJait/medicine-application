
const config = {
    API_URL: import.meta.env.VITE_API_URL || "/api",
    ENV: import.meta.env.MODE || "development",
};

Object.freeze(config);

export default config;
