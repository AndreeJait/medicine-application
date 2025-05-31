export interface User {
    id: string;
    name: string;
    email: string;
    nik?: string;
    is_active: boolean;
    position: string;
    role:string;
    permissions: Array<string>;
}
