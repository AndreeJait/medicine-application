import { RootState } from "@/types/store";
import { ActionContext } from "vuex";
import {MedicineState} from "@/types/medicine";
import {exportMedicineCSV} from "@/services/medicine.service.ts";

type MedicineContext = ActionContext<MedicineState, RootState>;

export const exportCSV = async ({ rootState }: MedicineContext): Promise<void> => {
    const token = rootState.auth.token;
    if (!token) {
        alert("Unauthorized");
        return;
    }

    const blob = await exportMedicineCSV(token);
    if (!blob) {
        alert("Failed to export CSV");
        return;
    }

    // Ambil filename dari header jika bisa (opsional)
    const filename = `medicine_export_${new Date().toISOString().slice(0, 10)}.csv`;

    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.setAttribute("download", filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
};
