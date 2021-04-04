import {RouteProps} from "react-router-dom";
import CategoryList from "../pages/category/PageList";
import CategoryCreate from "../pages/category/PageForm";
import CastMemberList from "../pages/cast-member/PageList";
import CastMemberCreate from "../pages/cast-member/PageForm";
import GenreCreate from "../pages/genre/PageForm";
import GenreList from "../pages/genre/PageList";
import Dashboard from "../pages/Dashboard";

export interface MyRouteProps extends RouteProps {
    label: string;
    name: string;
}

const routes: MyRouteProps[] = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        path: '/',
        component: Dashboard,
        exact: true
    },
    {
        name: 'categories.list',
        label: 'Listar categorias',
        path: '/categories',
        component: CategoryList,
        exact: true
    },
    {
        name: 'categories.create',
        label: 'Nova categoria',
        path: '/categories/create',
        component: CategoryCreate,
        exact: true
    },
    {
        name: 'cast_members.list',
        label: 'Membros do Elenco',
        path: '/cast_members',
        component: CastMemberList,
        exact: true
    },
    {
        name: 'cast_members.create',
        label: 'Novo Membro do Elenco',
        path: '/cast_members/create',
        component: CastMemberCreate,
        exact: true
    },
    {
        name: 'genres.list',
        label: 'Gêneros',
        path: '/genres',
        component: GenreList,
        exact: true
    },
    {
        name: 'genres.create',
        label: 'Novo Gênero',
        path: '/genres/create',
        component: GenreCreate,
        exact: true
    },
   
];

export default routes;