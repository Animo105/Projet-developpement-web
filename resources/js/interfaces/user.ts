import {Contact} from '@/interfaces/contact'
import {ParticipantList} from '@/interfaces/participant'
export interface User {
    id:number,
    given_name:string,
    family_name:string,
    email:string,
    phone:string,
    contacts:Contact[]
    participants:ParticipantList[]
}
