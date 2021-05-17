// @flow 
import * as React from 'react';
import {useEffect, useState, useRef, useReducer} from "react";
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
import Breadcrumbs from '../../components/Breadcrumbs';
import reducer, { Creators, INITIAL_STATE } from '../../store/filter';
import useFilter from '../../hooks/useFilter';

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

const debounceTime = 300;
const debouncedSearchTime = 300;

const Table = () => {
    const snackbar = useSnackbar();
    const subscribed = useRef(true);
    const [data, setData] = useState<Category[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const {
        columns,
        filterManager,
        filterState,
        debouncedFilterState,
        dispatch,
        totalRecords,
        setTotalRecords
    } = useFilter({
        columns: columnsDefinition,
        debounceTime: debounceTime,
        rowsPerPage: 10,
        rowsPerPageOptions: [10, 25, 50]
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
        filterManager.cleanSearchText(debouncedFilterState.search), 
        debouncedFilterState.pagination.page, 
        debouncedFilterState.pagination.per_page,
        debouncedFilterState.order,
    ]);

    async function getData() {
        setLoading(true);
        try {
            const {data} = await categoryHttp.list<ListResponse<Category>>({
                queryParams: {
                    search: filterManager.cleanSearchText(filterState.search),
                    page: filterState.pagination.page,
                    per_page: filterState.pagination.per_page,
                    sort: filterState.order.sort,
                    dir: filterState.order.dir,
                }
            })
            if (subscribed.current) {
                setData(data.data);
                setTotalRecords(data.meta.total);
                // setfilterState((prevState => (
                //     {
                //         ...prevState,
                //         pagination: {
                //             ...prevState.pagination,
                //             total: data.meta.total
                //         }
                //     }
                // )))
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
                debouncedSearchTime={debouncedSearchTime}
                options={{
                    serverSide: true,
                    responsive: "simple",
                    searchText: filterState.search as any,
                    page: filterState.pagination.page-1,
                    rowsPerPage: filterState.pagination.per_page,
                    count: totalRecords,
                    customToolbar: () => (
                        <FilterResetButton 
                            handleClick={() => dispatch(Creators.setReset())}
                        />
                    ),
                    onSearchChange: (value) => filterManager.changeSearch(value),
                    onChangePage: (page) =>  filterManager.changePage(page),
                    onChangeRowsPerPage: (perPage) => filterManager.changeRowsPerPage(perPage),
                    onColumnSortChange: (changedColumn: string, direction: 'asc' | 'desc') => 
                        filterManager.columnSortChange(changedColumn, direction)
                }} 
            />
        </MuiThemeProvider>
    );
};

export default Table;