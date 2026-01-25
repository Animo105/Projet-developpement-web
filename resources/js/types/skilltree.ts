export interface ClassItem {
    id: number;
    name: string;
    icon: string;
}

export interface ManaItem {
    id: number;
    name: string;
    icon: string;
}

export interface TreeMetadata {
    classes: ClassItem[];
    manas: ManaItem[];
}

export interface CardData {
    cardId: number;
    img: string;
    title: string;
    acquired: boolean;
}

export interface SkillNode {
    nodeId: number;
    parentId: number | null;
    position: {
        x: number;
        y: number;
    };
    blockedBy: number | null;
    state: 'acquired' | 'neutral-acquired' | 'available' | 'locked' | 'neutral';
    color: string;
    cards: CardData[];
}

export interface CanvasData {
    minX: number;
    minY: number;
    width: number;
    height: number;
}

export interface SkillTree {
    title: string;
    canvas: CanvasData;
    nodes: SkillNode[];
    skill_point: number;
}

// Alias for compatibility
export interface TreeNode extends SkillNode {}
export interface TreeData extends SkillTree {}
