export interface Tarif {
    id:number
    label:string
    type:string
    amount:number
}
export interface Entry {
    participant_id:number
    participant_name:string
    character_name:string
    telephone_number:string
    is_adult:boolean
    types: string[]
}

export interface EventShort {
    id:number
    title:string
}

export interface EventSummary {
    id:number,
    title:string,
    description:string,
    date:string
    participants:number
    can_register:boolean
}

export interface Spending {
    id:number
    name:string
    amount:number
}

export interface EventFull {
    id:number,
    title:string,
    description:string,
    start_date:string,
    end_date:string,
    date:string,
    profits:number,
    spendings:Spending[],
    end_date_registration:string,
    tarifs: Tarif[],
    event_linked:number | null,
    entries:Entry[],
    bill?:number,
}
