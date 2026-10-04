<?php

namespace App\Http\Controllers;

use App\{Program, ProgramParticipant, Participant, Audio, Comment};
use App\Console\Tool\SyncAppProgram;

use View, Config, Cache, Request, Response, Redirect;

/**
 * 节目控制器
 *
 * @author popfeng <popfeng@yeah.net> 2016-01-11
 */
class ProgramController extends Controller
{ 

    /**
     * 首页
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $isFlush = $request::get('flush');
        $keyword = $request::get('s');

        // get programs archive html
        if (empty($keyword)) {
            $archive = Cache::get(Program::INDEX_CACHE_KEY);
            if ($isFlush or empty($archive)) {
                $archive = $this->getProgramsArchiveHtml();
                Cache::forever(Program::INDEX_CACHE_KEY, $archive);
            }
        } else {
            $archive = $this->getProgramsArchiveHtml($keyword);
        }

        // render page
        return View::make('program.index.frame')->with('archive', $archive);
    }

    /**
     * App同期节目
     *
     * @return Redirect
     */
    public function apptoday()
    {
        return Redirect::to(sprintf(
            '/program/%s?from=apptoday',
            $this->getAppProgramDate()
        ));
    }

    /**
     * 详情
     *
     * @param int $date
     * @return void
     */
    public function detail($date)
    {
        // query the specified dates program and audio list
        $program = Program::dated($date)->enabled()->first();
        $audios = $this->getAudios($program);

        // get contributions info
        $contributers = $this->getProgramContributers($program->date);

        // get around pages
        $pages = (object) [
            'prev' => Program::find($program->id - 1),
            'next' => Program::find($program->id + 1)
        ];

        // TDK
        $title = $program->topic . ' (' . $program->date . ') ';
        $description = sprintf(
            '%s 期飞鱼秀 %s 回放在线收听,下载',
            $program->date,
            $program->topic
        );

        // render page
        return View::make('program.detail')
            ->with('appdate', $this->getAppProgramDate())
            ->with('program', $program)
            ->with('audios', $audios)
            ->with('pages', $pages)
            ->with('contributers', $contributers)
            ->with('title', $title)
            ->with('description', $description);
    }

    /**
     * 获取页面浏览数
     *
     * @param int $date
     * @return Response
     */
    public function getPvCounts($date)
    {
        Program::dated($date)->increment('view_counts');
        $counts = Program::dated($date)->value('view_counts');
        return Response::json(['total' => $counts]);
    }

    /**
     * 获取节目单HTML字符串
     *
     * @param string $keyword
     * @return string
     */
    private function getProgramsArchiveHtml($keyword = '')
    {
        // query program list, and sort by date
        $programs = Program::with('participants')
            ->enabled()
            ->orderBy('date', 'desc')
            ->get();

        // search
        if ($keyword) {
            $keywords = array_filter(explode(' ', $keyword));
            foreach ($keywords as $keyword) {
                $participantIds = Participant::searched($keyword)->lists('id');
                $programIds = ProgramParticipant::whereIn(
                    'participant_id', $participantIds
                )->lists('program_id')->flip()->toArray();
                foreach ($programs as $i => $program) {
                    if (false === strpos(strtolower($program->topic) , $keyword) and
                        ! isset($programIds[$program->id])) {
                        unset($programs[$i]);
                    }
                }
            }
        }

        // collect
        $list = [];
        foreach ($programs as $program) {
            $list[$program->dates->year][$program->dates->month][] = $program;
        }

        // render page
        return (string) View::make('program.index.archive')->with('list', $list);
    }

    /**
     * get audio list
     *
     * @param Program $program
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getAudios(&$program)
    {
        // filter by source, and sort by time
        $audios = $audiosCopy = $program->audios()->enabled()->get();

        $audioList = [];
        $audioGroups = $audios->groupBy('part');
        foreach ($audioGroups as $group) {
            foreach ($group as $i => $item) {
                if ($item->download) {
                    unset($group[$i]);
                }
            }
            $group = $group->groupBy('source');

            if (isset($group[Audio::SOURCE_DEFAULT])) {
		/*if (1 < $group->count()) {
                    unset($group[Audio::SOURCE_DEFAULT]);
                } else {
                    $audio = $group[Audio::SOURCE_DEFAULT][0];
                    $audio->url = qiniu_url($audio->url);
                }*/

                $audio = $group[Audio::SOURCE_DEFAULT][0];
                $url = $audio->url;
                if (substr($url, -5) === '.m3u8') {
                    $url = Config::get('filesystems.disks.qiniu.audio_prefix') . $url;
                }
                $audio->url = qiniu_url($url);
            }
            $audioList[] = $group->first()->first();
        }
        $audios = collect($audioList);

        // get download url
        $downloadUrls = [];
        foreach ($audiosCopy as $audio) {
            if ($audio->download) {
                $downloadUrls[$audio->part] = $audio->url;
            }
        }
        foreach ($audios as $audio) {
            $audio->download_url = $downloadUrls[$audio->part] ?? null;
        }

        return $audios;
    }

    /**
     * 获取本节目的贡献者
     *
     * @param int $date
     * @return array
     */
    private function getProgramContributers($date)
    {
        $contributers = [
            'topic'        => null,
            'participants' => null
        ];

        $logs = Comment::contributed($date)->get()->sortByDesc('id');
        foreach ($logs as $log) {
            $author = [
                'name' => $log->author_name,
                'url'  => $log->author_url
            ];

            if (null === $contributers['topic'] and
                Comment::STATUS['ENABLE'] === $log->has_topic) {
                $contributers['topic'] = $author;
            }
            if (null === $contributers['participants'] and
                Comment::STATUS['ENABLE'] === $log->has_participant) {
                $contributers['participants'] = $author;
            }
        }

        return $contributers;
    }

    /**
     * 获取App节目日期
     *
     * @return string
     */
    private function getAppProgramDate()
    {
        return Cache::get(SyncAppProgram::DATE_CACHE_KEY, '20040802');
    }
}
