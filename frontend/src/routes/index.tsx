import {RouteProps} from "react-router-dom";
import CategoryList from "../pages/category/PageList";
import CategoryCreate from "../pages/category/PageForm";
import CastMemberList from "../pages/cast-member/PageList";
import CastMemberCreate from "../pages/cast-member/PageForm";
import GenreCreate from "../pages/genre/PageForm";
import GenreList from "../pages/genre/PageList";
import VideoList from "../pages/video/PageList";
import VideoCreate from "../pages/video/PageForm";
import Dashboard from "../pages/Dashboard";
import UploadPage from "../pages/uploads";

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
        name: 'categories.edit',
        label: 'Editar categoria',
        path: '/categories/:id/edit',
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
        name: 'cast_members.edit',
        label: 'Novo Membro do Elenco',
        path: '/cast_members/:id/edit',
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
    {
        name: 'genres.edit',
        label: 'Editar Gênero',
        path: '/genres/:id/edit',
        component: GenreCreate,
        exact: true
    },
    {
        name: 'videos.list',
        label: 'Listar Vídeos',
        path: '/videos',
        component: VideoList,
        exact: true
    },
    {
        name: 'videos.create',
        label: 'Novo Vídeo',
        path: '/videos/create',
        component: VideoCreate,
        exact: true
    },
    {
        name: 'videos.edit',
        label: 'Editar Vídeo',
        path: '/videos/:id/edit',
        component: VideoCreate,
        exact: true
    },
    {
        name: 'uploads',
        label: 'Uploads',
        path: '/uploads',
        component: UploadPage,
        exact: true,
    }
   
];

export default routes;