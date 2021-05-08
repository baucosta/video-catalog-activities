// @flow 
import * as React from 'react';
import {useEffect, useState, useRef} from "react";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import categoryHttp from '../../utils/http/category-http';
import { BadgeNo, BadgeYes } from '../../components/Badge';
import { Category, ListResponse } from '../../utils/models';
import DefaultTable, {TableColumn, makeActionStyles} from '../../components/Table';
import { useSnackbar } from 'notistack';
import { IconButton, MuiThemeProvider, Theme } from '@material-ui/core';
import { Link } from 'react-router-dom';
import EditIcon from '@material-ui/icons/Edit';
import { FilterResetButton } from '../../components/Table/FilterResetButton';

interface Pagination {
    page: number;
    total: number;
    per_page: number;
}

interface Order {
    sort: string | null;
    dir: string | null;
}

interface SearchState {
    search: any;
    pagination: Pagination;
    order: Order;
}

const columnsDefinition: TableColumn[] = [
    {
        name: "id",
        label: "ID",
        width: "30%",
        options: {
            sort: false
        },
    },
    {
        name: "name",
        label: "Nome",
        width: "43%",
    },
    {
        name: "is_active",
        label: "Ativo?",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return value ? <BadgeYes/> : <BadgeNo/>;
            }
        },
        width: "4%",
    },
    {
        name: "created_at",
        label: "Criado em",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        },
        width: "10%",
    },
    {
        name: "actions",
        label: "Ações",
        width: "13%",
        options: {
            sort: false,
            customBodyRender(value, tableMeta) {
                return (
                    <IconButton
                        color={'secondary'}
                        component={Link}
                        to={`/categories/${tableMeta.rowData[0]}/edit`}
                    >
                        <EditIcon fontSize={'inherit'} />
                    </IconButton>
                )
            }
        },
    },
];


const Table = () => {

    const initialState = {
        search: '',
        pagination: {
            page: 1, 
            total: 0,
            per_page: 10
        },
        order: {
            sort: null,
            dir: null
        }
    }
    const snackbar = useSnackbar();
    const subscribed = useRef(true);
    const [data, setData] = useState<Category[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const [searchState, setSearchState] = useState<SearchState>(initialState);

    const columns = columnsDefinition.map(column => {
        return column.name === searchState.order.sort
        ? {
            ...column,
            options: {
                ...column.options,
                sortDirection: searchState.order.dir as any
            }
        } : column;
    });

    useEffect(() => {

    }, [])

    useEffect(() => {
        subscribed.current = true;
        getData();

        return () => {
            subscribed.current = false;
        }
    }, [
        searchState.search, 
        searchState.pagination.page, 
        searchState.pagination.per_page,
        searchState.order,
    ]);

    async function getData() {
        setLoading(true);
        try {
            const {data} = await categoryHttp.list<ListResponse<Category>>({
                queryParams: {
                    search: searchState.search,
                    page: searchState.pagination.page,
                    per_page: searchState.pagination.per_page,
                    sort: searchState.order.sort,
                    dir: searchState.order.dir,
                }
            })
            if (subscribed.current) {
                setData(data.data);
                setSearchState((prevState => (
                    {
                        ...prevState,
                        pagination: {
                            ...prevState.pagination,
                            total: data.meta.total
                        }
                    }
                )))
            }
        } catch(error) {
            if (categoryHttp.isCancelledRequest(error)) {
                return;
            }
            snackbar.enqueueSnackbar(
                'Não foi possível carregar as informações',
                {variant: 'error'}
              );

        } finally {
            setLoading(false);
        }
    }

    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length - 1)}>
            <DefaultTable 
                title="Listagem de categorias"
                columns={columns} 
                data={data}
                loading={loading}
                options={{
                    serverSide: true,
                    responsive: "simple",
                    searchText: searchState.search,
                    page: searchState.pagination.page-1,
                    rowsPerPage: searchState.pagination.per_page,
                    count: searchState.pagination.total,
                    customToolbar: () => (
                        <FilterResetButton handleClick={() => {
                            setSearchState(initialState)
                        }} />
                    ),
                    onSearchChange: (value) => setSearchState((prevState => (
                        {
                            ...prevState,
                            search: value,
                            pagination: {
                                ...prevState.pagination,
                                page: 1
                            }
                        }
                    ))),
                    onChangePage: (page) => setSearchState((prevState => (
                        {
                            ...prevState,
                            pagination: {
                                ...prevState.pagination,
                                page: page + 1
                            }
                        }
                    ))),
                    onChangeRowsPerPage: (perPage) => setSearchState((prevState => (
                        {
                            ...prevState,
                            pagination: {
                                ...prevState.pagination,
                                per_page: perPage
                            }
                        }
                    ))),
                    onColumnSortChange: (changedColumn: string, direction: 'asc' | 'desc') => setSearchState((prevState => (
                        {
                            ...prevState,
                            order: {
                                sort: changedColumn,
                                dir: direction.includes('desc') ? 'desc ' : 'asc',
                            }
                        }
                    ))),
                }} 
            />
        </MuiThemeProvider>
    );
};

export default Table;