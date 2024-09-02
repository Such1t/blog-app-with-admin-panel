import Inline from '../blots/inline';
declare class Bold extends Inline {
    static blotName: string;
    static tagName: string[];
    static create(): Node;
    static formats(): boolean;
    optimize(context: {
        [key: string]: any;
    }): void;
}
export default Bold;
