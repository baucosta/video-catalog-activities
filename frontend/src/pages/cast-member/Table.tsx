// @flow 
import * as React from 'react';
import {useEffect, useState, useRef} from "react";
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";
import { CastMember, ListResponse, CastMemberTypeMap } from '../../utils/models';
import castMemberHttp from '../../utils/http/cast-member-http';
import DefaultTable, {TableColumn, makeActionStyles, MuiDataTableRefComponent} from '../../components/Table';
import { useSnackbar } from 'notistack';
import useFilter from '../../hooks/useFilter';
import { IconButton, MuiThemeProvider } from '@material-ui/core';
import { FilterResetButton } from '../../components/Table/FilterResetButton';
import { Creators } from '../../store/filter';
import { Link } from 'react-router-dom';
import EditIcon from '@material-ui/icons/Edit';
import *  as yup from '../../utils/vendor/yup';
import { invert } from 'lodash';

const castMemberNames = CastMemberTypeMap.map(v => v.description);

// export interface CastMember {
//     id: string;
//     name: string;
//     type: number;
// }


const columnsDefinition: TableColumn[] = [
    {
        name: "id",
        label: "ID",
        width: "24%",
        options: {
            sort: false,
            filter: false
        },
    },
    {
        name: "name",
        label: "Nome",
        width: "43%",
        options: {
            filter: false
        },
    },
    {
        name: "type",
        label: "Tipo",
        options: {
            filterOptions: {
               names: castMemberNames
            },
            customBodyRender(value, tableMeta, updateValue) {
                return CastMemberTypeMap
                .filter(resp => resp.type === value)
                .map(resp => resp.description);
            },
        },
        width: "10%",
    },
    {
        name: "created_at",
        label: "Criado em",
        options: {
            filter: false,
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
            filter: false,
            customBodyRender(value, tableMeta) {
                return (
                    <IconButton
                        color={'secondary'}
                        component={Link}
                        to={`/cast_members/${tableMeta.rowData[0]}/edit`}
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
const rowsPerPage = 15;
const rowsPerPageOptions = [15, 25, 50]

const Table = () => {
    const snackbar = useSnackbar();
    const subscribed = useRef(true);
    const [data, setData] = useState<CastMember[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const tableRef = useRef() as React.MutableRefObject<MuiDataTableRefComponent>;

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
        rowsPerPage,
        rowsPerPageOptions,
        tableRef,
        extraFilter: {
            createValidationSchema: () => {
                return yup.object().shape({
                    type: yup.string()
                        .nullable()
                        .transform(value => {
                            return !value || !castMemberNames.includes(value) ? undefined : value;
                        })
                        .default(null)
                })
            },
            formatSearchParam: (debouncedState) => {
                return debouncedState.extraFilter ? {
                    ...(
                        debouncedState.extraFilter.type && 
                        {type: debouncedState.extraFilter.type}
                    )
                } : undefined
            },
            getStateFromURL: (queryParams) => {
                return {
                    type: queryParams.get('type')
                }
            }
        }
    });

    const indexColumnType = columns.findIndex(c => c.name === 'type');
    const columnType = columns[indexColumnType];
    const typeFilterValue = filterState.extraFilter && filterState.extraFilter.type as never;
    (columnType.options as any).filterList = typeFilterValue ? [typeFilterValue] : []

    const serverSideFilterList = columns.map(column => []);
    if (typeFilterValue) {
        serverSideFilterList[indexColumnType] = [typeFilterValue];
    }

    useEffect(() => {
        subscribed.current = true;
        filterManager.pushHistory();
        getData();

        return () => {
            subscribed.current = false;
        }
    }, [
        filterManager.cleanSearchText(debouncedFilterState.search), 
        debouncedFilterState.pagination.page, 
        debouncedFilterState.pagination.per_page,
        debouncedFilterState.order,
        JSON.stringify(debouncedFilterState.extraFilter)
    ]);

    async function getData() {
        setLoading(true);
        try {
            const {data} = await castMemberHttp.list<ListResponse<CastMember>>({
                queryParams: {
                    search: filterManager.cleanSearchText(filterState.search),
                    page: filterState.pagination.page,
                    per_page: filterState.pagination.per_page,
                    sort: filterState.order.sort,
                    dir: filterState.order.dir,
                    ...(
                        debouncedFilterState.extraFilter &&
                        debouncedFilterState.extraFilter.type &&
                        {type: invert(CastMemberTypeMap)[debouncedFilterState.extraFilter.type]}
                    )
                }
            })
            if (subscribed.current) {
                setData(data.data);
                setTotalRecords(data.meta.total);
            }
        } catch(error) {
            if (castMemberHttp.isCancelledRequest(error)) {
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

    /*useEffect(() => {
        let isSubscribed = true;
        (async () => {
            const {data} = await castMemberHttp.list<ListResponse<CastMember>>()
            if (isSubscribed) {
                setData(data.data)
            }
        })()

        return () => {
            isSubscribed = false;
        }
    }, []);*/

    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length - 1)}>
            <DefaultTable 
                title="Listagem de Membros do elenco"
                columns={columns} 
                data={data}
                loading={loading}
                debouncedSearchTime={debouncedSearchTime}
                ref={tableRef}
                options={{
                    serverSide: true,
                    // serverSideFilterList,
                    responsive: "simple",
                    searchText: filterState.search as any,
                    page: filterState.pagination.page-1,
                    rowsPerPage: filterState.pagination.per_page,
                    rowsPerPageOptions,
                    count: totalRecords,
                    onFilterChange: (column, filterList) => {
                        const columnIndex = columns.findIndex(c => c.name === column);
                        filterManager.changeExtraFilter({
                            [column as any]: filterList[columnIndex].length ? filterList[columnIndex][0] : null
                        })
                    },
                    customToolbar: () => (
                        <FilterResetButton 
                            handleClick={() => filterManager.resetFilter()}
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