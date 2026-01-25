export interface Bill {
    entries:Record<number, Record<string, boolean>>
    locked:Record<number, Record<string, boolean>>
}
