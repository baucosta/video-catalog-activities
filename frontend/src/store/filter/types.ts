import {AnyAction} from 'redux';

export interface Pagination {
    page: number;
    per_page: number;
}

export interface Order {
    sort: string | null;
    dir: string | null;
}

export interface State {
    search: string | {value, [key: string]: any} | null;
    pagination: Pagination;
    order: Order;
    extraFilter?: {value, [key: string]: any};
}

export interface SetSearchAction extends AnyAction {
    payload: {
        search: string | {value, [key: string]: any} | null
    }
}

export interface SetPageAction extends AnyAction {
    payload: {
        page: number
    }
}

export interface SetPerPageAction extends AnyAction {
    payload: {
        perPage: number
    }
}

export interface SetOrderAction extends AnyAction {
    payload: {
        sort: string | null,
        dir: string
    }
}

export interface SetResetAction extends AnyAction {
    payload: {
       state: State,
    }
}

export interface UpdateExtraFilterAction extends AnyAction {
    payload: {
        value, [key: string]: any
    }
}

export type Actions = SetSearchAction 
    | SetPageAction 
    | SetPerPageAction 
    | SetOrderAction 
    | UpdateExtraFilterAction
    | SetResetAction;