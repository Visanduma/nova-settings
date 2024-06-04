import Empty from './pages/NoSettings'
import Settings from './pages/Settings'

Nova.booting((app, store) => {
    Nova.inertia('NovaSettings', Settings)
    Nova.inertia('Empty', Empty)
})
