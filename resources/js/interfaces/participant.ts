export interface Participant {
    id:number
    name:string
    character_name:string
    is_adult:boolean

}

interface Character {
    id:number
    name:string
}

export interface ParticipantList {
    id:number
    name:string
    is_adult:boolean
    character?:Character
}
