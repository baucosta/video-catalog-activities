// @flow 
import * as React from 'react';
import MUIDataTable, {MUIDataTableColumn} from 'mui-datatables';
import {useEffect, useState} from "react";
import { httpVideo } from '../../utils/http';
import format from "date-fns/format";
import parseISO from "date-fns/parseISO";

export const CastMemberTypeMap = [
    {
        value: 1,
        description: 'Diretor',
    },
    {
        value: 2,
        description: 'Ator',
    },
];

export interface CastMember {
    id: string;
    name: string;
    type: number;
}


const columnsDefinition: MUIDataTableColumn[] = [
    {
        name: "name",
        label: "Nome",
    },
    {
        name: "type",
        label: "Tipo",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return CastMemberTypeMap
                .filter(resp => resp.value === value)
                .map(resp => resp.description);
            }
        }
    },
    {
        name: "created_at",
        label: "Criado em",
        options: {
            customBodyRender(value, tableMeta, updateValue) {
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    },
];


type Props = {
    
};
const Table = (props: Props) => {

    const [data, setData] = useState([]);

    useEffect(() => {
        (async function getCastMembers() {
            const {data} = await httpVideo.get('cast_members')
            setData(data.data)
        })()
    }, []);

    return (
        <MUIDataTable 
            title="Listagem do elenco"
            columns={columnsDefinition} 
            data={data} />
    );
};

export default Table;