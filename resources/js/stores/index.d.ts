declare module "@store/index" {
    const storeModules: {
        auth: any;
        [key: string]: any;
    };
    export default storeModules;
}
