// src/services/dashboard.service.ts
import api from "@/plugins/axios";

export const getDashboardSummary = () =>
    api.get("/dashboard/summary", {
        params: {
            usecase: "summary",
            source: "web",
        },
    });

export const getStockChart = () =>
    api.get("/dashboard/stock-chart", {
        params: {
            usecase: "stock chart",
            source: "web",
        },
    });
