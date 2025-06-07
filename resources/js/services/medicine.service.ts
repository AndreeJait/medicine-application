import api from "@/plugins/axios";
import Cookies from "js-cookie";

// ✅ 1. Get list of medicines (with pagination + search)
export const getMedicines = (page = 1, perPage = 10, search = "") =>
    api.get("/medicines", {
        params: {
            page,
            per_page: perPage,
            name: search,
            usecase: "for get medicine",
            source: "web",
        },
    });

// ✅ 2. Get a single medicine by ID
export const getMedicineById = (id: number) =>
    api.get(`/medicines/${id}?source=web&usecase=get medicine`);

// ✅ 3. Create medicine (without image)
export const createMedicine = (payload: object) =>
    api.post("/medicines", payload);

// ✅ 4. Update existing medicine
export const updateMedicine = (id: number, payload: object) =>
    api.put(`/medicines/${id}`, payload);

// ✅ 5. Delete medicine
export const deleteMedicine = (id: number) =>
    api.delete(`/medicines/${id}`, {
        data: {
            request_header: {
                source: "web",
                usecase: "delete medicine",
            },
        },
    });

// ✅ 6. Upload images (FormData)
export const uploadMedicineImages = (medicineId: number, files: File[]) => {
    const formData = new FormData();

    formData.append(
        "request_header",
        JSON.stringify({
            usecase: "postman",
            source: "postman",
        })
    );

    files.forEach((file) => {
        if (
            file instanceof File &&
            file.size > 0 &&
            ["image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp"].includes(file.type)
        ) {
            formData.append("images[]", file, file.name);
        }
    });

    return api.post(`/medicines/${medicineId}/images`, formData, {
        headers: {
            Authorization: `Bearer ${Cookies.get("token")}`,
        },
    });
};

// ✅ 7. Delete specific image from a medicine
export const deleteMedicineImage = (imageId: number) =>
    api.delete(`/medicines/images/${imageId}`, {
        data: {
            request_header: {
                usecase: "postman",
                source: "postman",
            },
        },
        headers: {
            Authorization: `Bearer ${Cookies.get("token")}`,
        },
    });

// ✅ 8. Get image blob for preview
export const getMedicineImageBlob = async (
    imageId: number
): Promise<string | null> => {
    try {
        const res = await api.get(
            `/medicines/images/${imageId}?source=postman&usecase=get%20usecase`,
            {
                responseType: "blob",
            }
        );
        return URL.createObjectURL(res.data);
    } catch (err) {
        console.warn("Failed to get image blob:", err);
        return null;
    }
};

// ✅ 9. Export CSV (raw fetch to get blob)
export const exportMedicineCSV = async (
    token: string
): Promise<Blob | null> => {
    try {
        const response = await fetch(
            `${import.meta.env.VITE_API_URL}/medicines/export?source=postman&usecase=get%20usecase`,
            {
                method: "GET",
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            }
        );
        if (!response.ok) throw new Error("Failed to export CSV");
        return await response.blob();
    } catch (err) {
        console.error("Export CSV error:", err);
        return null;
    }
};

/**
 * Get detail of a single medicine
 */
export const getMedicineDetail = (id: number) =>
    api.get(`/medicines/${id}`, {
        params: {
            source: "web",
            usecase: "for get medicine",
        },
    });

/**
 * Get stock history (with pagination & filter)
 */
export const getStockHistory = (
    medicineId: number,
    page = 1,
    perPage = 10,
    start_date = "",
    end_date = ""
) =>
    api.get(`/medicines/${medicineId}/history`, {
        params: {
            page,
            per_page: perPage,
            source: "web",
            usecase: "get history",
            start_date,
            end_date,
        },
    });

/**
 * Stock in (add stock)
 */
export const stockIn = (medicineId: number, amount: number, note = "") =>
    api.post(`/medicines/${medicineId}/stock-in`, {
        request_header: {
            source: "web",
            usecase: "check in stock",
        },
        amount,
        note,
    });

/**
 * Stock out (remove stock)
 */
export const stockOut = (medicineId: number, amount: number, note = "") =>
    api.post(`/medicines/${medicineId}/stock-out`, {
        request_header: {
            source: "web",
            usecase: "check out stock",
        },
        amount,
        note,
    });

/**
 * Export stock history as CSV
 */
export const exportStockHistoryCSV = async (
    medicineId: number,
    start_date = "",
    end_date = ""
): Promise<Blob | null> => {
    const token = Cookies.get("token");
    if (!token) return null;

    try {
        const url = `${import.meta.env.VITE_API_URL}/medicines/${medicineId}/history/export?source=web&usecase=get history&start_date=${start_date}&end_date=${end_date}`;
        const response = await fetch(url, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });
        if (!response.ok) throw new Error("Export failed");
        return await response.blob();
    } catch (error) {
        console.warn("Export history CSV failed:", error);
        return null;
    }
};


export const getGlobalStockHistory = (
    page = 1,
    perPage = 10,
    start_date = "",
    end_date = "",
    type = ""
) => {
    return api.get("/stock-histories", {
        params: {
            page,
            per_page: perPage,
            source: "postman",
            usecase: "get history",
            start_date,
            end_date,
            type,
        },
    });
};

export const exportGlobalStockHistoryCSV = async (
    start_date = "",
    end_date = "",
    type = "",
    token = ""
): Promise<Blob | null> => {
    try {
        const query = new URLSearchParams({
            source: "postman",
            usecase: "get history",
        });

        if (start_date) query.append("start_date", start_date);
        if (end_date) query.append("end_date", end_date);
        if (type) query.append("type", type);

        const url = `${import.meta.env.VITE_API_URL || "http://localhost:8000/api"}/stock-histories/export?${query}`;

        const response = await fetch(url, {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        });

        if (!response.ok) throw new Error("Export failed");

        return await response.blob();
    } catch (error) {
        console.warn("Global Stock Export CSV failed:", error);
        return null;
    }
};
