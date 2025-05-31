import {MedicineState} from "@/types/medicine";

export const SET_BY_KEY = (state: MedicineState, param: {key: string, value: any}) => state[param.key] = param.value;
